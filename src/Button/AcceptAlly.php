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
use ShockedPlot7560\FactionMaster\Event\AllianceCreateEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class AcceptAlly extends Button {
	public function __construct(InvitationEntity $request) {
		$this->setSlug(self::ACCEPT_ALLY)
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
							$factionPlayer = MainAPI::getFactionOfPlayer($player->getName());
							if (!$factionPlayer->haveMaxAlly()) {
								$factionRequest = MainAPI::getFaction($request->getSenderString());
								if ($factionRequest->haveMaxAlly()) {
									$message = Utils::getText($player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $request->sender]);
									MainAPI::setAlly($request->getReceiverString(), $request->getSenderString());
									Utils::newMenuSendTask(new MenuSendTask(
										function () use ($request) {
											return MainAPI::isAlly($request->getReceiverString(), $request->getSenderString());
										},
										function () use ($request, $player, $message) {
											(new AllianceCreateEvent($player, $request->getSenderString(), $request->getReceiverString()))->call();
											MainAPI::removeInvitation($request->getSenderString(), $request->getReceiverString(), InvitationEntity::ALLIANCE_INVITATION);
											Utils::newMenuSendTask(new MenuSendTask(
												function () use ($request) {
													return !MainAPI::areInInvitation($request->getSenderString(), $request->getReceiverString(), InvitationEntity::ALLIANCE_INVITATION);
												},
												function () use ($request, $player, $message) {
													(new InvitationAcceptEvent($player, $request))->call();
													Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [$message]);
												},
												function () use ($player) {
													Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
												}
											));
										},
										function () use ($player) {
											Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
										}
									));
								} else {
									$message = Utils::getText($player->getName(), "MAX_ALLY_REACH_OTHER");
									Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [$message]);
								}
							} else {
								$message = Utils::getText($player->getName(), "MAX_ALLY_REACH");
								Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [$message]);
							}
						} else {
							Utils::processMenu(RouterFactory::get(RouteSlug::ALLIANCE_REQUEST_RECEIVE_ROUTE), $player, [$request]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_ACCEPT_REQUEST"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_ACCEPT_REQUEST_ALLY"),
				]);
			})
			->setPermissions([
				PermissionIds::PERMISSION_ACCEPT_ALLIANCE_DEMAND
			])
			->setImgPack("textures/img/true");
	}
}