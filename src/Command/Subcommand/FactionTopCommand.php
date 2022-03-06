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

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Command\Argument\EnumArgument;
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
use function array_keys;
use function count;
use function implode;
use function str_replace;

class FactionTopCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_TOP_DESCRIPTION";
	}

	protected function prepare(): void {
		$this->registerArgument(0, new EnumArgument("slug", array_keys(LeaderboardManager::getAll())));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if ($sender instanceof Player) {
			if (count($args) == 0) {
				Utils::processMenu(RouterFactory::get(RouteSlug::LEADERBOARD_ROUTE), $sender);
			} elseif (isset($args["slug"])) {
				$slug = $args["slug"];
				$leadEntity = LeaderboardManager::getLeaderboard($slug);
				if (!$leadEntity instanceof EntityLeaderboard) {
					Utils::processMenu(RouterFactory::get(RouteSlug::LEADERBOARD_ROUTE), $sender);
				}
				if (!LeaderboardManager::isRegister($args["slug"])) {
					$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_SCOREBOARD_INVALID_SLUG", ["list" => implode(", ", array_keys(LeaderboardManager::getAll()))]));
					return;
				}
				$leadEntity->setLimit(ConfigManager::getConfig()->get("leaderboard-menu-limit") ?? 10);
				//TODO: HACK
				$leadInstance = new Leaderboard($slug, $sender->getPosition());
				FactionMaster::getInstance()->getServer()->getAsyncPool()->submitTask(new DatabaseTask(
					$leadEntity->getSqlQuery(),
					[],
					function (array $result) use ($leadInstance, $sender) {
						/** @var FactionEntity[] $result */
						$content = "\n";
						foreach ($result as $faction) {
							$newLine = $leadInstance->getBodyLign();
							$newLine = str_replace(["{factionName}", "{level}", "{power}"], [$faction->getName(), $faction->getLevel(), $faction->getPower()], $newLine);
							$content .= $newLine . "\n";
						}
						Utils::processMenu(RouterFactory::get(RouteSlug::LEADERBOARD_SHOW_ROUTE), $sender, [$content, $leadInstance->getHeaderLign()]);
					},
					FactionEntity::class
				));
			}
		}
	}
}