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
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Member extends Button {
	public function __construct(string $name, int $rank) {
		$this->setSlug(self::MEMBER)
			->setContent(function (string $player) use ($name, $rank) {
				$text = $name . "\n";
				switch ($rank) {
					case Ids::RECRUIT_ID:
						$text .= "§7" . Utils::getText($player, "RECRUIT_RANK_NAME");
						break;
					case Ids::MEMBER_ID:
						$text .= "§7" . Utils::getText($player, "MEMBER_RANK_NAME");
						break;
					case Ids::COOWNER_ID:
						$text .= "§7" . Utils::getText($player, "COOWNER_RANK_NAME");
						break;
					case Ids::OWNER_ID:
						$text .= "§7" . Utils::getText($player, "OWNER_RANK_NAME");
						break;
				}
				return $text;
			})
			->setCallable(function (Player $player) {
				Utils::processMenu(RouterFactory::get(RouteSlug::MEMBERS_VIEW_ROUTE), $player);
			});
	}
}