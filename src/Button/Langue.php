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
use ShockedPlot7560\FactionMaster\Event\PlayerChangeLanguageEvent;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class Langue extends Button {
	public function __construct(string $lang) {
		foreach (Utils::getConfigLang("languages-name") as $slug => $langN) {
			if ($langN === $lang) {
				$slugG = $slug;
			}
		}
		$this->setSlug($lang)
			->setContent(function (string $player) use ($lang) {
				$userLang = MainAPI::getPlayerLang($player);
				return $lang . (Utils::getConfigLang("languages-name")[$userLang] === $lang ? ("\n" . Utils::getText($player, "CURRENT_LANG")) : "");
			})
			->setCallable(function (Player $player) use ($lang) {
				foreach (Utils::getConfigLang("languages-name") as $key => $value) {
					if ($value === $lang) {
						$lang = $key;
					}
				}
				MainAPI::changeLanguage($player->getName(), $lang);
				Utils::newMenuSendTask(new MenuSendTask(
					function () use ($lang, $player) {
						return MainAPI::getPlayerLang($player->getName()) === $lang;
					},
					function () use ($player, $lang) {
						(new PlayerChangeLanguageEvent($player, $lang))->call();
						Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player);
					},
					function () use ($player) {
						Utils::processMenu(RouterFactory::get(RouteSlug::MAIN_ROUTE), $player, [Utils::getText($player->getName(), "ERROR")]);
					}
				));
			})
			->setImgPack((isset($slugG) ? "textures/img/lang/" . $slugG : ""));
	}
}