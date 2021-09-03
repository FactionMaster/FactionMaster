<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560
 * @link https://github.com/ShockedPlot7560
 *
 *
 */

namespace ShockedPlot7560\FactionMaster\Button;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Event\FactionDeleteEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLeaveEvent;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class LeaveDelete extends Button {

    public function __construct() {
        parent::__construct(
            "leavingButton",
            function ($Player) {
                return Utils::getText($Player, "BUTTON_LEAVE_DELETE_FACTION");
            },
            $this->leaveDeleteButtonFunction(),
            [],
            "textures/img/trash",
            SimpleForm::IMAGE_TYPE_PATH
        );
    }

    private function leaveDeleteButtonFunction(): callable {
        return function (Player $Player) {
            $UserEntity = MainAPI::getUser($Player->getName());
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($UserEntity->rank == Ids::OWNER_ID) {
                Utils::processMenu(
                    RouterFactory::get(ConfirmationMenu::SLUG),
                    $Player,
                    [
                        $this->callConfirmDelete($Faction),
                        Utils::getText($Player->getName(), "CONFIRMATION_TITLE_DELETE_FACTION", ['factionName' => $Faction->name]),
                        Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_DELETE_FACTION"),
                    ]
                );
            } else {
                $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                Utils::processMenu(
                    RouterFactory::get(ConfirmationMenu::SLUG),
                    $Player,
                    [
                        $this->callConfirmLeave($Faction),
                        Utils::getText($Player->getName(), "CONFIRMATION_TITLE_LEAVE_FACTION", ['factionName' => $Faction->name]),
                        Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_LEAVE_FACTION"),
                    ]
                );
            }
        };
    }

    private function callConfirmLeave(FactionEntity $Faction): callable {
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) {
                return;
            }

            if ($data) {
                $message = Utils::getText($Player->getName(), "SUCCESS_LEAVE_FACTION");
                MainAPI::removeMember($Faction->name, $Player->getName());
                (new FactionLeaveEvent($Player, $Faction))->call();
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
            } else {
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }

    private function callConfirmDelete(FactionEntity $Faction): callable {
        return function (Player $Player, $data) use ($Faction) {
            if ($data === null) {
                return;
            }

            if ($data) {
                $message = Utils::getText($Player->getName(), "SUCCESS_DELETE_FACTION");
                MainAPI::removeFaction($Faction->name);
                Utils::newMenuSendTask(new MenuSendTask(
                    function () use ($Faction) {
                        return !MainAPI::getFaction($Faction->name) instanceof FactionEntity;
                    },
                    function () use ($Player, $Faction, $message) {
                        (new FactionDeleteEvent($Player, $Faction))->call();
                        Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
                    },
                    function () use ($Player) {
                        Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                    }
                ));
            } else {
                Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player);
            }
        };
    }
}