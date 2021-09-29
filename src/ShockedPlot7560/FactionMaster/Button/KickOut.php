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
use ShockedPlot7560\FactionMaster\Event\MemberKickOutEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\ConfirmationRoute;
use ShockedPlot7560\FactionMaster\Route\ManageMemberRoute as MembersManageMember;
use ShockedPlot7560\FactionMaster\Route\MembersManageRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class KickOut extends Button {

    const SLUG = "kickOut";

    public function __construct(UserEntity $member) {
        $this->setSlug(self::SLUG)
            ->setContent(function (string $player) {
                return Utils::getText($player, "BUTTON_KICK_OUT");
            })
            ->setCallable(function (Player $player) use ($member) {
                Utils::processMenu(RouterFactory::get(ConfirmationRoute::SLUG), $player, [
                    function (Player $player, $data) use ($member) {
                        $faction = MainAPI::getFactionOfPlayer($player->getName());
                        if ($data === null) {
                            return;
                        }

                        if ($data) {
                            $message = Utils::getText($player->getName(), "SUCCESS_KICK_OUT", ['playerName' => $member->getName()]);
                            MainAPI::removeMember($faction->getName(), $member->getName());
                            Utils::newMenuSendTask(new MenuSendTask(
                                function () use ($player, $faction) {
                                    $user = MainAPI::getUser($player->getName());
                                    return $user instanceof UserEntity && $user->getFactionName() !== $faction->getName();
                                },
                                function () use ($player, $faction, $message) {
                                    $member = MainAPI::getUser($player->getName());
                                    (new MemberKickOutEvent($player, $faction, $member))->call();
                                    Utils::processMenu(RouterFactory::get(MembersManageRoute::SLUG), $player, [$message]);
                                },
                                function () use ($player) {
                                    Utils::processMenu(RouterFactory::get(MembersManageRoute::SLUG), $player, [Utils::getText($player->getName(), "ERROR")]);
                                }
                            ));
                        } else {
                            Utils::processMenu(RouterFactory::get(MembersManageMember::SLUG), $player, [$member]);
                        }
                    },
                    Utils::getText($player->getName(), "CONFIRMATION_TITLE_KICK_OUT"),
                    Utils::getText($player->getName(), "CONFIRMATION_CONTENT_KICK_OUT"),
                ]);
            })
            ->setPermissions([
                PermissionIds::PERMISSION_KICK_MEMBER
            ]);
    }

}