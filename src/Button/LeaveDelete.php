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
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Event\FactionDeleteEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLeaveEvent;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class LeaveDelete extends Button {
	public function __construct() {
		$this->setSlug(self::LEAVE_DELETE)
			->setContent(function ($player) {
				return Utils::getText($player, "BUTTON_LEAVE_DELETE_FACTION");
			})
			->setCallable($this->leaveDeleteButtonFunction())
			->setImgPack("textures/img/trash");
	}

	private function leaveDeleteButtonFunction(): callable {
		return function (Player $player) {
			$userEntity = MainAPI::getUser($player->getName());
			$faction = MainAPI::getFactionOfPlayer($player->getName());
			if ($userEntity->getRank() == Ids::OWNER_ID) {
				Utils::processMenu(
					RouterFactory::get(RouteSlug::CONFIRMATION_ROUTE),
					$player,
					[
						$this->callConfirmDelete($faction),
						Utils::getText($player->getName(), "CONFIRMATION_TITLE_DELETE_FACTION", ['factionName' => $faction->getName()]),
						Utils::getText($player->getName(), "CONFIRMATION_CONTENT_DELETE_FACTION"),
					]
				);
			} else {
				$faction = MainAPI::getFactionOfPlayer($player->getName());
				Utils::processMenu(
					RouterFactory::get(RouteSlug::CONFIRMATION_ROUTE),
					$player,
					[
						$this->callConfirmLeave($faction),
						Utils::getText($player->getName(), "CONFIRMATION_TITLE_LEAVE_FACTION", ['factionName' => $faction->getName()]),
						Utils::getText($player->getName(), "CONFIRMATION_CONTENT_LEAVE_FACTION"),
					]
				);
			}
		};
	}

	private function callConfirmLeave(FactionEntity $faction): callable {
		return function (Player $player, $data) use ($faction) {
			if ($data === null) {
				return;
			}

			if ($data) {
				$message = Utils::getText($player->getName(), "SUCCESS_LEAVE_FACTION");
				MainAPI::removeMember($faction->getName(), $player->getName());
				(new FactionLeaveEvent($player, $faction))->call();
				Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [$message]);
			} else {
				Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player);
			}
		};
	}

	private function callConfirmDelete(FactionEntity $faction): callable {
		return function (Player $player, $data) use ($faction) {
			if ($data === null) {
				return;
			}

			if ($data) {
				$message = Utils::getText($player->getName(), "SUCCESS_DELETE_FACTION");
				MainAPI::removeFaction($faction->getName());
				Utils::newMenuSendTask(new MenuSendTask(
					function () use ($faction) {
						return !MainAPI::getFaction($faction->getName()) instanceof FactionEntity;
					},
					function () use ($player, $faction, $message) {
						(new FactionDeleteEvent($player, $faction))->call();
						Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [$message]);
					},
					function () use ($player) {
						Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
					}
				));
			} else {
				Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player);
			}
		};
	}
}