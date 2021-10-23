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
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Event\AllianceBreakEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\AllianceOptionRoute;
use ShockedPlot7560\FactionMaster\Route\ConfirmationRoute;
use ShockedPlot7560\FactionMaster\Route\ManageAllianceRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class BreakAlly extends Button {
	const SLUG = "breakAlly";

	public function __construct(FactionEntity $ally) {
		$this->setSlug(self::SLUG)
			->setContent(function (string $player) {
				return Utils::getText($player, "BUTTON_BREAK_ALLIANCE");
			})
			->setCallable(function (Player $player) use ($ally) {
				Utils::processMenu(RouterFactory::get(ConfirmationRoute::SLUG), $player, [
					function (Player $player, $data) use ($ally) {
						$faction = MainAPI::getFactionOfPlayer($player->getName());
						if ($data === null) {
							return;
						}

						if ($data === true) {
							MainAPI::removeAlly($faction->getName(), $ally->getName());
							Utils::newMenuSendTask(new MenuSendTask(
								function () use ($faction, $ally) {
									return MainAPI::isAlly($faction->getName(), $ally->getName());
								},
								function () use ($player, $faction, $ally) {
									$event = new AllianceBreakEvent($player, $faction, $ally);
									$event->call();
									Utils::processMenu(RouterFactory::get(AllianceOptionRoute::SLUG), $player, [Utils::getText($player->getName(), "SUCCESS_BREAK_ALLIANCE", ['name' => $ally->name])]);
								},
								function () use ($player) {
									Utils::processMenu(RouterFactory::get(AllianceOptionRoute::SLUG), $player, [Utils::getText($player->getName(), "ERROR")]);
								}
							));
						} else {
							Utils::processMenu(RouterFactory::get(ManageAllianceRoute::SLUG), $player, [$ally]);
						}
					},
					Utils::getText($player->getName(), "CONFIRMATION_TITLE_BREAK_ALLIANCE"),
					Utils::getText($player->getName(), "CONFIRMATION_CONTENT_BREAK_ALLIANCE"),
				]);
			})
			->setPermissions([
				PermissionIds::PERMISSION_BREAK_ALLIANCE
			]);
	}
}