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

namespace ShockedPlot7560\FactionMaster\Button\Collection;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Back;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\AllianceBreakEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\AllianceMainMenu;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\ManageAlliance;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAllianceCollection extends Collection {

    const SLUG = "manageAlliance";

    public function __construct() {
        parent::__construct(self::SLUG);
        $this->registerCallable(self::SLUG, function (Player $player, UserEntity $User, FactionEntity $Ally) {
            $this->register(new Button(
                "breakAlly",
                function (string $Player) {
                    return Utils::getText($Player, "BUTTON_BREAK_ALLIANCE");
                },
                function (Player $Player) use ($Ally) {
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                        function (Player $Player, $data) use ($Ally) {
                            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                            if ($data === null) {
                                return;
                            }

                            if ($data === true) {
                                $message = Utils::getText($Player->getName(), "SUCCESS_BREAK_ALLIANCE", ['name' => $Ally->name]);
                                if (!MainAPI::removeAlly($Faction->name, $Ally->name)) {
                                    $message = Utils::getText($Player->getName(), "ERROR");
                                }

                                (new AllianceBreakEvent($Player, $Faction->name, $Ally->name))->call();
                                Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player, [$message]);
                            } else {
                                Utils::processMenu(RouterFactory::get(ManageAlliance::SLUG), $Player, [$Ally]);
                            }
                        },
                        Utils::getText($Player->getName(), "CONFIRMATION_TITLE_BREAK_ALLIANCE"),
                        Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_BREAK_ALLIANCE"),
                    ]);
                },
                [PermissionIds::PERMISSION_BREAK_ALLIANCE]
            ));
            $this->register(new Back(AllianceMainMenu::SLUG));
        });
    }
}