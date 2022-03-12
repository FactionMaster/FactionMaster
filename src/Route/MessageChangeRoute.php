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
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MessageChangeEvent;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\CustomForm;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function is_string;

class MessageChangeRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "messageChangeRoute";

	public function getSlug(): string {
		return self::MESSAGE_CHANGE_ROUTE;
	}

	public function getPermissions(): array {
		return [
			PermissionIds::PERMISSION_CHANGE_FACTION_MESSAGE
		];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::FACTION_OPTION_ROUTE);
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

			if (isset($data[1]) && is_string($data[1])) {
				$faction = $this->getFaction();
				$message = $data[1];
				MainAPI::changeMessage($faction->getName(), $message);
				Utils::newMenuSendTask(new MenuSendTask(
					function () use ($faction, $message) {
						return MainAPI::getFaction($faction->getName())->getMessage() === $message;
					},
					function () use ($player, $faction, $message) {
						$oldMessage = $faction->getMessage();
						$faction->setMessage($message);
						(new MessageChangeEvent($player, $faction, $oldMessage))->call();
						Utils::processMenu($this->getBackRoute(), $player, [Utils::getText($player->getName(), "SUCCESS_MESSAGE_UPDATE")]);
					},
					function () use ($player) {
						Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
					}
				));
				return;
			}
			Utils::processMenu($this, $player, [Utils::getText($player->getName(), "ERROR")]);
		};
	}

	protected function getForm(string $message = ""): CustomForm {
		$menu = new CustomForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "CHANGE_MESSAGE_TITLE"));
		$menu->addLabel($message . $this->getFaction()->getMessage());
		$menu->addInput(Utils::getText($this->getUserEntity()->getName(), "CHANGE_MESSAGE_INPUT_CONTENT"), "", $this->getFaction()->getMessage());
		return $menu;
	}
}