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

namespace ShockedPlot7560\FactionMaster\Entity;

use pocketmine\level\Level;
use pocketmine\level\Position;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function explode;
use function str_replace;

class ScoreboardEntity extends FactionMasterEntity {
	private $tick = 200;

	public function getName(): string {
		return self::getEntityName();
	}

	public static function getEntityName(): string {
		return "ScoreboardEntity";
	}

	public function initEntity(): void {
		parent::initEntity();
		$this->setImmobile(true);
		$this->setScale(0.0000001);
		$this->setHealth(1000);
		$this->setNameTagAlwaysVisible(true);
	}

	public function onUpdate(int $currentTick): bool {
		if ($this->tick == 200) {
			$id = $this->getId();
			$level = $this->getLevel();
			if ($level instanceof Level) {
				$levelName = $level->getName();
				Main::getInstance()->getServer()->getAsyncPool()->submitTask(new DatabaseTask(
					LeaderboardManager::$queryList["faction"],
					[],
					function (array $result) use ($levelName, $id) {
						$nametag = Utils::getConfig("faction-scoreboard-header") . "\n";
						foreach ($result as $faction) {
							$newLine = Utils::getConfig("faction-scoreboard-lign");
							$newLine = str_replace(["{factionName}", "{level}", "{power}"], [$faction->getName(), $faction->getLevel(), $faction->getPower()], $newLine);
							$nametag .= $newLine . "\n";
						}
						$entity = Main::getInstance()->getServer()->getLevelByName($levelName)->getEntity($id);
						if ($entity !== null) {
							$entity->setNameTag($nametag);
						}
					},
					FactionEntity::class
				));
			}
			$coordinates = ConfigManager::getLeaderboardConfig()->get("faction-position");
			if ($coordinates !== false && $coordinates !== "") {
				$coordinates = explode("|", $coordinates);
				if (count($coordinates) == 4) {
					$levelName = $coordinates[3];
					$level = Main::getInstance()->getServer()->getLevelByName($levelName);
					if ($level instanceof Level) {
						$position = new Position((float) $coordinates[0], (float) $coordinates[1], (float) $coordinates[2], $level);
						if ($this->getPosition() !== $position) {
							$level->loadChunk((float) $coordinates[0] >> 4, (float) $coordinates[2] >> 4);
							$this->teleport($position);
						}
					} else {
						Main::getInstance()->getLogger()->notice("An unknow world was set in leaderboard.yml, can't load faction leaderboard");
					}
				}
			}
			$this->tick--;
		} elseif ($this->tick == 0) {
			$this->tick = 200;
		} else {
			$this->tick--;
		}
		return parent::onUpdate($currentTick);
	}
}
