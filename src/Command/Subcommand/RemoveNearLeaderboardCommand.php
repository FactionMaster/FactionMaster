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
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\Position;
use ShockedPlot7560\FactionMaster\Event\LeaderboardRemoveEvent;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function array_keys;
use function explode;
use function floor;
use function join;

class RemoveNearLeaderboardCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_SCOREBOARD_REMOVE_DESCRIPTION";
	}

	protected function prepare(): void {
		$this->setPermission("factionmaster.leaderboard.place");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if ($sender instanceof Player) {
			if ($sender->hasPermission("factionmaster.leaderboard.remove")) {
				/** @var Position|null $prec */
				$prec = null;
				foreach (array_keys(LeaderboardManager::getAllSession()) as $coordonate) {
					$coordonate = explode("|", $coordonate);
					if ($sender->getWorld()->getDisplayName() === $coordonate[3]) {
						$level = Main::getInstance()->getServer()->getWorldManager()->getWorldByName($coordonate[3]);
						$coordonate = new Position((int) $coordonate[0], (int) $coordonate[1], (int) $coordonate[2], $level);
						$playerVector = new Vector3(
							$sender->getPosition()->getX(),
							$sender->getPosition()->getY(),
							$sender->getPosition()->getZ()
						);
						$distance = $coordonate->distance($playerVector);
						if ($prec == null || $prec->distance($playerVector) > $distance) {
							$prec = $coordonate;
						}
					}
				}
				if ($prec == null) {
					$sender->sendMessage(Utils::getText("", "COMMAND_SCOREBOARD_NO_NEAREST"));
					return;
				}
				$coordonate = join("|", [
					$prec->getX(),
					$prec->getY(),
					$prec->getZ(),
					$prec->getWorld()->getDisplayName()
				]);
				$leaderboards = ConfigManager::getLeaderboardConfig()->get("leaderboards");
				foreach ($leaderboards as $index => $data) {
					$pos = explode("|", $data["position"]);
					if (floor((int) $pos[0]) == $prec->getFloorX() && floor((int) $pos[1]) == $prec->getFloorY() && floor((int) $pos[2]) == $prec->getFloorZ() && $pos[3] == $prec->getWorld()->getDisplayName()) {
						(new LeaderboardRemoveEvent(new Vector3($pos[0], $pos[1], $pos[2])))->call();
						unset($leaderboards[$index]);
					}
				}
				$config = ConfigManager::getLeaderboardConfig();
				$config->set("leaderboards", $leaderboards);
				$config->save();
				LeaderboardManager::dispawnLeaderboard($coordonate);
				$sender->sendMessage(Utils::getText("", "COMMAND_SCOREBOARD_REMOVE_SUCCESS"));
			} else {
				$sender->sendMessage(Utils::getText("", "DONT_PERMISSION"));
			}
		}
	}
}