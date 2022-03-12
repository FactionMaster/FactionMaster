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
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class TopFactionRoute extends RouteBase {
	/** @deprecated */
	const SLUG = "topFactionRoute";

	public function getSlug(): string {
		return self::TOP_FACTION_ROUTE;
	}

	public function getPermissions(): array {
		return [];
	}

	public function getBackRoute(): ?Route {
		return RouterFactory::get(self::MAIN_ROUTE);
	}

	public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->init($player, $userEntity, $userPermissions, $params);
		$this->getPlayer()->sendForm($this->getForm($params[0]));
	}

	public function call(): callable {
		return function (Player $player, $data) {
			if ($data === null) {
				return;
			}
			Utils::processMenu($this->getBackRoute(), $player);
		};
	}

	protected function getForm(array $top): SimpleForm {
		$menu = new SimpleForm($this->call());
		$menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "TOP_FACTION_TITLE"));
		$content = '';
		foreach ($top as $key => $faction) {
			$content .= Utils::getText($this->getUserEntity()->getName(), "TOP_FACTION_LINE", [
				'rank' => ($key + 1),
				'factionName' => $faction->getName(),
				'level' => $faction->getLevel(),
			]);
		}
		$menu->addButton(Utils::getText($this->getUserEntity()->getName(), "BUTTON_BACK"));
		$menu->setContent($content);
		return $menu;
	}
}