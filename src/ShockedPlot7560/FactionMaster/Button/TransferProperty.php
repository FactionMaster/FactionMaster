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

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Event\FactionPropertyTransferEvent;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\ManageMember as MembersManageMember;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class TransferProperty extends Button {

    public function __construct(UserEntity $Member) {
        parent::__construct(
            "transferProperty",
            function (string $Player) {
                return Utils::getText($Player, "BUTTON_TRANSFER_PROPERTY");
            },
            function (Player $Player) use ($Member) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Member) {
                        if ($data === null) {
                            return;
                        }

                        if ($data) {
                            $message = Utils::getText($Player->getName(), "SUCCESS_TRANSFER_PROPERTY", ['playerName' => $Member->name]);
                            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
                            $Faction->members[$Player->getName()] = Ids::COOWNER_ID;
                            $Faction->members[$Member->name] = Ids::OWNER_ID;
                            Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                                new DatabaseTask(
                                    "UPDATE " . FactionTable::TABLE_NAME . " SET members = :members WHERE name = :name",
                                    [
                                        'members' => \base64_encode(\serialize($Faction->members)),
                                        'name' => $Faction->name,
                                    ],
                                    function () use ($Faction) {
                                        MainAPI::$factions[$Faction->name] = $Faction;
                                    }
                                )
                            );
                            $user = MainAPI::getUser($Player->getName());
                            $user->rank = Ids::COOWNER_ID;
                            Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                                new DatabaseTask(
                                    "UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name",
                                    [
                                        'rank' => Ids::COOWNER_ID,
                                        'name' => $user->name,
                                    ],
                                    function () use ($user) {
                                        MainAPI::$users[$user->name] = $user;
                                        (new MemberChangeRankEvent($user))->call();
                                    }
                                )
                            );
                            $userj = MainAPI::getUser($Member->name);
                            $userj->rank = Ids::OWNER_ID;
                            Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                                new DatabaseTask(
                                    "UPDATE " . UserTable::TABLE_NAME . " SET rank = :rank WHERE name = :name",
                                    [
                                        'rank' => Ids::OWNER_ID,
                                        'name' => $userj->name,
                                    ],
                                    function () use ($userj, $user, $Player, $Member, $message) {
                                        MainAPI::$users[$userj->name] = $userj;
                                        MainAPI::$users[$user->name] = $user;
                                        (new MemberChangeRankEvent($userj))->call();
                                        (new FactionPropertyTransferEvent($Player, $Member, $Player->getName()))->call();
                                        Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
                                    }
                                )
                            );
                        } else {
                            Utils::processMenu(RouterFactory::get(MembersManageMember::SLUG), $Player, [$Member]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_TRANSFER_PROPERTY"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_TRANSFER_PROPERTY"),
                ]);
            }
        );
    }

}