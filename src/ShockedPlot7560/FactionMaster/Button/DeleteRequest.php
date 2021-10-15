<?php

declare(strict_types=1);

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
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Event\InvitationRefuseEvent;
use ShockedPlot7560\FactionMaster\Route\ConfirmationRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DeleteRequest extends Button {
	const SLUG = "deleteRequest";

	public function __construct(InvitationEntity $request, string $panelSlug, string $backPanelSlug, array $permissions = []) {
		$this->setSlug(self::SLUG)
			->setContent(function (string $player) {
				return Utils::getText($player, "BUTTON_REFUSE_REQUEST");
			})
			->setCallable(function (Player $player) use ($request, $panelSlug, $backPanelSlug) {
				Utils::processMenu(RouterFactory::get(ConfirmationRoute::SLUG), $player, [
					function (Player $player, $data) use ($request, $panelSlug, $backPanelSlug) {
						if ($data === null) {
							return;
						}

						if ($data) {
							$message = Utils::getText($player->getName(), "SUCCESS_DELETE_REQUEST", ['name' => $request->getSenderString()]);
							MainAPI::removeInvitation($request->getSenderString(), $request->getReceiverString(), $request->getType());
							Utils::newMenuSendTask(new MenuSendTask(
								function () use ($request) {
									return !MainAPI::areInInvitation($request->getSenderString(), $request->getReceiverString(), $request->getType());
								},
								function () use ($request, $player, $panelSlug, $message) {
									(new InvitationRefuseEvent($player, $request))->call();
									Utils::processMenu(RouterFactory::get($panelSlug), $player, [$message]);
								},
								function () use ($player, $panelSlug) {
									Utils::processMenu(RouterFactory::get($panelSlug), $player, [Utils::getText($player->getName(), "ERROR")]);
								}
							));
						} else {
							Utils::processMenu(RouterFactory::get($backPanelSlug), $player, [$request]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_DELETE_REQUEST"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_DELETE_REQUEST"),
				]);
			})
			->setPermissions($permissions)
			->setImgPack("textures/img/false");
	}
}