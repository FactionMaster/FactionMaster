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

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;
use ShockedPlot7560\FactionMaster\Manager\DatabaseManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionTable implements TableInterface {

	/** @var PDO */
	private $PDO;

	const TABLE_NAME = "factionmaster_faction";
	const SLUG = "factionmaster_faction";

	public function init(): self {
		$tableName = self::TABLE_NAME;
		$auto_increment = Utils::getConfig("PROVIDER") === DatabaseManager::MYSQL_PROVIDER ? "AUTO_INCREMENT" : "AUTOINCREMENT";
		$visibility = Utils::getConfig("default-faction-visibility");
		$xp = Utils::getConfig("default-faction-xp");
		$maxPlayer = Utils::getConfig("default-player-limit");
		$maxAlly = Utils::getConfig("default-ally-limit");
		$maxClaim = Utils::getConfig("default-claim-limit");
		$maxHome = Utils::getConfig("default-home-limit");
		$power = Utils::getConfig("default-power");
		$this->PDO->query("CREATE TABLE IF NOT EXISTS `$tableName` ( 
            `id` INTEGER PRIMARY KEY $auto_increment, 
            `name` TEXT NOT NULL, 
            `members` TEXT NOT NULL, 
            `visibility` TINYINT UNSIGNED NOT NULL DEFAULT '$visibility', 
            `xp` SMALLINT UNSIGNED NOT NULL DEFAULT '$xp', 
            `level` TINYINT UNSIGNED NOT NULL, 
            `description` TEXT NOT NULL, 
            `message` TEXT NOT NULL, 
            `ally` TEXT NOT NULL, 
            `maxPlayer` TINYINT UNSIGNED NOT NULL DEFAULT '$maxPlayer',
            `maxAlly` TINYINT UNSIGNED NOT NULL DEFAULT '$maxAlly', 
            `maxClaim` TINYINT UNSIGNED NOT NULL DEFAULT '$maxClaim', 
            `maxHome` TINYINT UNSIGNED NOT NULL DEFAULT '$maxHome', 
            `power` SMALLINT UNSIGNED NOT NULL DEFAULT '$power', 
            `permissions` TEXT NOT NULL, 
            `date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP)");
		return $this;
	}

	public function __construct(PDO $PDO) {
		$this->PDO = $PDO;
	}
}