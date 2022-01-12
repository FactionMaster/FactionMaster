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
use pocketmine\world\Position;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\FactionMaster;
use ShockedPlot7560\FactionMaster\Leaderboard\EntityLeaderboard;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Leaderboard;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function str_replace;
use function strtoupper;

class LeaderboardItem extends Button {
	public function __construct(string|EntityLeaderboard $slug) {
		if ($slug instanceof EntityLeaderboard) {
			$slug = $slug->getSlug();
		}
		$this->setSlug(self::LEADERBOARD_ITEM)
			->setContent(function (string $player) use ($slug) {
				return Utils::getText($player, "BUTTON_LEADERBOARD_" . strtoupper($slug));
			})
			->setCallable(function (Player $player) use ($slug) {
				$leadEntity = LeaderboardManager::getLeaderboard($slug);
				if (!$leadEntity instanceof EntityLeaderboard) {
					Utils::processMenu(RouterFactory::get(RouteSlug::LEADERBOARD_ROUTE), $player);
				}
				$leadEntity->setLimit(ConfigManager::getConfig()->get("leaderboard-menu-limit") ?? 10);
				//TODO: HACK
				$leadInstance = new Leaderboard($slug, new Position(0, 0, 0, FactionMaster::getInstance()->getServer()->getWorldManager()->getDefaultWorld()));
				FactionMaster::getInstance()->getServer()->getAsyncPool()->submitTask(new DatabaseTask(
					$leadEntity->getSqlQuery(),
					[],
					function (array $result) use ($leadInstance, $player) {
						/** @var FactionEntity[] $result */
						$content = "\n";
						foreach ($result as $faction) {
							$newLine = $leadInstance->getBodyLign();
							$newLine = str_replace(["{factionName}", "{level}", "{power}"], [$faction->getName(), $faction->getLevel(), $faction->getPower()], $newLine);
							$content .= $newLine . "\n";
						}
						Utils::processMenu(RouterFactory::get(RouteSlug::LEADERBOARD_SHOW_ROUTE), $player, [$content, $leadInstance->getHeaderLign()]);
					},
					FactionEntity::class
				));
			});
	}
}