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

use ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Event\AllianceCreateEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\AllianceDemandList;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class AcceptAlly extends Button {

    public function __construct(InvitationEntity $Request) {
        parent::__construct(
            "acceptAlly",
            function (string $Player) {
                return Utils::getText($Player, "BUTTON_ACCEPT_REQUEST");
            },
            function (Player $Player) use ($Request) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Request) {
                        if ($data === null) {
                            return;
                        }

                        if ($data) {
                            $FactionPlayer = MainAPI::getFactionOfPlayer($Player->getName());
                            if (count($FactionPlayer->ally) < $FactionPlayer->max_ally) {
                                $FactionRequest = MainAPI::getFaction($Request->sender);
                                if (count($FactionRequest->ally) < $FactionRequest->max_ally) {
                                    $message = Utils::getText($Player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $Request->sender]);
                                    MainAPI::setAlly($Request->receiver, $Request->sender);
                                    Utils::newMenuSendTask(new MenuSendTask(
                                        function () use ($Request) {
                                            return MainAPI::isAlly($Request->receiver, $Request->sender);
                                        },
                                        function () use ($Request, $Player, $message) {
                                            (new AllianceCreateEvent($Player, $Request->sender, $Request->receiver))->call();
                                            MainAPI::removeInvitation($Request->sender, $Request->receiver, "alliance");
                                            Utils::newMenuSendTask(new MenuSendTask(
                                                function () use ($Request) {
                                                    return !MainAPI::areInInvitation($Request->sender, $Request->receiver, "alliance");
                                                },
                                                function () use ($Request, $Player, $message) {
                                                    (new InvitationAcceptEvent($Player, $Request))->call();
                                                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [$message]);
                                                },
                                                function () use ($Player) {
                                                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                                }
                                            ));
                                        },
                                        function () use ($Player) {
                                            Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                        }
                                    ));

                                } else {
                                    $message = Utils::getText($Player->getName(), "MAX_ALLY_REACH_OTHER");
                                    Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [$message]);
                                }
                            } else {
                                $message = Utils::getText($Player->getName(), "MAX_ALLY_REACH");
                                Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [$message]);
                            }
                        } else {
                            Utils::processMenu(RouterFactory::get(AllianceDemandList::SLUG), $Player, [$Request]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_ACCEPT_REQUEST"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_ACCEPT_REQUEST_ALLY"),
                ]);
            },
            [PermissionIds::PERMISSION_ACCEPT_ALLIANCE_DEMAND],
            "textures/img/true",
            SimpleForm::IMAGE_TYPE_PATH
        );
    }
}