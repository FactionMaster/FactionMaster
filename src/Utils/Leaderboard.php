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

namespace ShockedPlot7560\FactionMaster\Utils;

use InvalidArgumentException;
use pocketmine\math\Vector3;
use pocketmine\utils\Config;
use pocketmine\world\Position;
use pocketmine\world\World;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use function count;
use function explode;

class Leaderboard {

	/** @var string */
	private $slug;
	/** @var Position */
	private $position;

	/**
	 * @param Position|string[] $position
	 */
	public function __construct(string $slug, Position|string $position) {
		if (!$position instanceof Position) {
			$position = explode("|", $position);
			if (count($position) >= 4) {
				$world = Main::getInstance()->getServer()->getWorldManager()->getWorldByName($position[3]);
				if ($world instanceof World) {
					$position = new Position((int) $position[0], (int) $position[1], (int) $position[2], $world);
				} else {
					throw new InvalidArgumentException("Invalid world given");
				}
			} else {
				throw new InvalidArgumentException("position argument must be contain valid data");
			}
		}
		$this->position = $position;
		$this->slug = $slug;
	}

	public function getPosition(): Position {
		return $this->position;
	}

	public function getVector3(): Vector3 {
		return new Vector3(
			$this->getPosition()->getX(),
			$this->getPosition()->getY(),
			$this->getPosition()->getZ()
		);
	}

	public function getRawCoordonate(): string {
		return Utils::getRawCoordonate($this->getPosition());
	}

	public function getSlug(): string {
		return $this->slug;
	}

	public function getWorld(): ?World {
		return $this->getPosition()->getWorld();
	}

	public function getConfig(): Config {
		return LeaderboardManager::getLeaderboard($this->getSlug())->getConfig();
	}

	public function getHeaderLign(): string {
		return $this->getConfig()->get($this->getSlug() . "-leaderboard-header");
	}

	public function getBodyLign(): string {
		return $this->getConfig()->get($this->getSlug() . "-leaderboard-body");
	}

	public function dispawn(): void {
		LeaderboardManager::dispawnLeaderboard($this->getSlug(), $this->getRawCoordonate());
	}

	public function spawn(): void {
		LeaderboardManager::placeScoreboard($this);
	}
}