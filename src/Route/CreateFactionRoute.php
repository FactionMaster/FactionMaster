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

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionCreateEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function in_array;
use function is_string;
use function strlen;

class CreateFactionRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "createFactionRoute";

	public function getSlug(): string {
		return self::CREATE_FACTION_ROUTE;
	}

	public function getPermissions(): array {
		return [];
	}

	public function getBackRoute(): Route {
		return RouterFactory::get(self::MAIN_ROUTE);
	}

	/**
	 * @param array|null $params Give to first item the message to print if wanted
	 */
	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);

		$message = "";
		if (isset($params[0]) && is_string($params[0])) {
			$message = $params[0];
		}

		$player->sendForm($this->getForm($message));
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}

			$factionName = $data[1];
			$factionRequested = MainAPI::getFaction($factionName);
			if ($factionName !== "") {
				if (!$factionRequested instanceof FactionEntity) {
					if (strlen($factionName) >= ConfigManager::getConfig()->get("min-faction-name-length")
							&& strlen($factionName) <= ConfigManager::getConfig()->get("max-faction-name-length")) {
						if (!in_array($factionName, (array) Utils::getConfig("banned-faction-name"), true)) {
							$event = new FactionCreateEvent($player, $factionName);
							MainAPI::addFaction($factionName, $player->getName());
							Utils::newMenuSendTask(new MenuSendTask(
								function () use ($factionName) {
									return MainAPI::getFaction($factionName) instanceof FactionEntity;
								},
								function () use ($player, $event) {
									$event->call();
									Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_CREATE_FACTION")]);
								},
								function () use ($player) {
									Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
								}
							));
						} else {
							Utils::processMenu($this, $player, [Utils::getText($player->getName(), "CREATE_FACTION_PANEL_CONTENT_BANNED")]);
						}
					} else {
						Utils::processMenu($this, $player, [Utils::getText($player->getName(), "MAX_MIN_REACH_NAME", ["min" => Utils::getConfig("min-faction-name-length"), "max" => Utils::getConfig("max-faction-name-length")])]);
					}
				} else {
					Utils::processMenu($this, $player, [Utils::getText($player->getName(), "FACTION_NAME_ALREADY_EXIST")]);
				}
			} else {
				Utils::processMenu($this->getBackRoute(), $player);
			}
		};
	}

	protected function getForm(string $message = ""): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "CREATE_FACTION_PANEL_TITLE"));
		$menu->addLabel(Utils::getText($this->getUserEntity()->getName(), "CREATE_FACTION_PANEL_CONTENT") . "\n" . $message);
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "CREATE_FACTION_PANEL_INPUT_CONTENT"));
		return $menu;
	}
}