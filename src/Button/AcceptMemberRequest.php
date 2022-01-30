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

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;

class AcceptMemberRequest extends Button {
	public function __construct(InvitationEntity $request) {
		$this->setSlug(self::ACCEPT_MEMBER_REQUEST)
			->setContent(function (string $player) {
				return Utils::getText($player, "BUTTON_ACCEPT_REQUEST");
			})
			->setCallable(function (Player $player) use ($request) {
				Utils::processMenu(RouterFactory::get(RouteSlug::CONFIRMATION_ROUTE), $player, [
					function (Player $player, $data) use ($request) {
						if ($data === null) {
							return;
						}

						if ($data) {
							$faction = MainAPI::getFaction($request->getSenderString());
							if (count($faction->getMembers()) < $faction->getMaxPlayer()) {
								$message = Utils::getText($player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $request->getSenderString()]);
								MainAPI::addMember($request->getSenderString(), $request->getReceiverString());
								Utils::newMenuSendTask(new MenuSendTask(
									function () use ($request) {
										$user = MainAPI::getUser($request->getReceiverString());
										return $user instanceof UserEntity && $user->getFactionName() === $request->getSenderString();
									},
									function () use ($request, $player, $faction, $message) {
										(new FactionJoinEvent($request->getReceiverString(), $faction))->call();
										MainAPI::removeInvitation($request->getSenderString(), $request->getReceiverString(), $request->getType());
										Utils::newMenuSendTask(new MenuSendTask(
											function () use ($request) {
												return !MainAPI::areInInvitation($request->sender, $request->getReceiverString(), $request->getType());
											},
											function () use ($request, $player, $message) {
												(new InvitationAcceptEvent($player, $request))->call();
												Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [$message]);
											},
											function () use ($player) {
												Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
											}
										));
									},
									function () use ($player) {
										Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
									}
								));
							} else {
								$message = Utils::getText($player->getName(), "MAX_PLAYER_REACH");
								Utils::processMenu(RouterFactory::get(RouteSlug::JOIN_REQUEST_RECEIVE_ROUTE), $player, [$message]);
							}
						} else {
							Utils::processMenu(RouterFactory::get(RouteSlug::MANAGE_JOIN_REQUEST_ROUTE), $player, [$request]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_ACCEPT_REQUEST"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_ACCEPT_REQUEST"),
				]);
			})
			->setImgPack("textures/img/true");
	}
}