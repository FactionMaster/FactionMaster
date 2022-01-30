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
use ShockedPlot7560\FactionMaster\Event\InvitationDeleteEvent;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DeleteInvitation extends Button {
	public function __construct(InvitationEntity $invitation, string $panelSlug, array $permissions = []) {
		$this->setSlug(self::DELETE_INVITATION)
			->setContent(function (string $player) {
				return Utils::getText($player, "BUTTON_DELETE_INVITATION");
			})
			->setCallable(function (Player $player) use ($invitation, $panelSlug) {
				Utils::processMenu(RouterFactory::get(RouteSlug::CONFIRMATION_ROUTE), $player, [
					function (Player $player, $data) use ($invitation, $panelSlug) {
						if ($data === null) {
							return;
						}

						if ($data) {
							$message = Utils::getText($player->getName(), "SUCCESS_DELETE_INVITATION", ['name' => $invitation->getReceiverString()]);
							MainAPI::removeInvitation($invitation->getSenderString(), $invitation->getReceiverString(), $invitation->getType());
							Utils::newMenuSendTask(new MenuSendTask(
								function () use ($invitation) {
									return !MainAPI::areInInvitation($invitation->getSenderString(), $invitation->getReceiverString(), $invitation->getType());
								},
								function () use ($invitation, $player, $panelSlug, $message) {
									(new InvitationDeleteEvent($player, $invitation))->call();
									Utils::processMenu(RouterFactory::get($panelSlug), $player, [$message]);
								},
								function () use ($player, $panelSlug) {
									Utils::processMenu(RouterFactory::get($panelSlug), $player, [Utils::getText($player->getName(), "ERROR")]);
								}
							));
						} else {
							Utils::processMenu(RouterFactory::get($panelSlug), $player, [$invitation]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_DELETE_INVITATION"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_DELETE_INVITATION"),
				]);
			})
			->setPermissions($permissions)
			->setImgPack("textures/img/trash");
	}
}