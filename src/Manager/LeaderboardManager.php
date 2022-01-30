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
namespace ShockedPlot7560\FactionMaster\Manager;

use Exception;
use pocketmine\math\Vector3;
use pocketmine\world\particle\FloatingTextParticle;
use ShockedPlot7560\FactionMaster\Event\LeaderboardUpdateEvent;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Leaderboard\EntityLeaderboard;
use ShockedPlot7560\FactionMaster\Leaderboard\FactionLevelLeaderboard;
use ShockedPlot7560\FactionMaster\Leaderboard\FactionPowerLeaderboard;
use ShockedPlot7560\FactionMaster\Utils\Leaderboard;
use function explode;
use function join;
use function strtolower;

class LeaderboardManager {

	/** @var Main */
	private static $main;
	/** @var FloatingTextParticle[][] */
	private static $session = [];
	/** @var EntityLeaderboard[] */
	public static $leaderboards = [];

	public static function init(Main $main) {
		self::$main = $main;

		self::registerLeaderboard(new FactionLevelLeaderboard($main));
		self::registerLeaderboard(new FactionPowerLeaderboard($main));
	}

	public static function registerLeaderboard(EntityLeaderboard $leaderboard, bool $override = false): void {
		if (self::isRegister($leaderboard->getSlug()) && !$override) {
			throw new Exception("Leaderboard id already register, conflicts detected");
		}
		$slug = strtolower($leaderboard->getSlug());
		self::$leaderboards[$slug] = $leaderboard;
	}

	public static function removeLeaderboard(string $slug): void {
		$slug = strtolower($slug);
		if (isset(self::$leaderboards[$slug])) {
			unset(self::$leaderboards[$slug]);
		}
	}

	public static function isRegister(string $slug): bool {
		$slug = strtolower($slug);
		return isset(self::$leaderboards[$slug]);
	}

	public static function getLeaderboard(string $slug): ?EntityLeaderboard {
		$slug = strtolower($slug);
		return self::$leaderboards[$slug] ?? null;
	}

	/**
	 * @return EntityLeaderboard[]
	 */
	public static function getAll(): array {
		return self::$leaderboards;
	}

	/**
	 * @return FloatingTextParticle[][]
	 */
	public static function getAllSession(): array {
		return self::$session;
	}

	public static function addSession(string $coordonate, FloatingTextParticle $particle): void {
		self::$session[$coordonate][] = $particle;
	}

	public static function placeScoreboard(Leaderboard $leaderboard, ?array $players = null): void {
		if (self::isRegister($leaderboard->getSlug())) {
			if (($class = self::getLeaderboard($leaderboard->getSlug())) instanceof EntityLeaderboard) {
				$class->place($leaderboard, $players);
			}
		}
	}

	public static function dispawnLeaderboard(string $coordinates): void {
		if (isset(self::$session[$coordinates])) {
			/** @var FloatingTextParticle[] $particles */
			$particles = self::$session[$coordinates];
			$coordinates = explode("|", $coordinates);
			$vector = new Vector3((int) $coordinates[0], (int) $coordinates[1], (int) $coordinates[2]);
			foreach ($particles as $particle) {
				$particle->setInvisible(true);
				foreach ($particle->encode($vector) as $packet) {
					foreach (self::$main->getServer()->getOnlinePlayers() as $player) {
						$player->getNetworkSession()->sendDataPacket($packet);
					}
				}
			}

			unset(self::$session[join("|", $coordinates)]);
		}
	}

	public static function updateLeaderboards(): void {
		$leaderboards = ConfigManager::getLeaderboardConfig()->get("leaderboards");
		if ($leaderboards === false) {
			$leaderboards = [];
		}
		foreach ($leaderboards as $leaderboard) {
			if ($leaderboard["active"] == true) {
				LeaderboardManager::dispawnLeaderboard($leaderboard["position"]);
				$class = self::getLeaderboard($leaderboard["slug"]);
				if ($class instanceof EntityLeaderboard) {
					$entity = new Leaderboard($class->getSlug(), $leaderboard["position"]);
					(new LeaderboardUpdateEvent($entity))->call();
					LeaderboardManager::placeScoreboard($entity);
				}
			}
		}
	}
}