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

namespace ShockedPlot7560\FactionMaster\Task;

use PDO;
use pocketmine\scheduler\AsyncTask;
use ShockedPlot7560\FactionMaster\Manager\DatabaseManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function call_user_func;

class DatabaseTask extends AsyncTask {
	private $provider;
	private $db;
	private $query;
	private $args;
	private $callable;
	private $class;

	public function __construct(string $query, array $args, ?callable $callable = null, ?string $class = null) {
		$this->query = $query;
		$this->args = $args;
		$this->class = $class;
		$this->callable = $callable;
		$this->provider = Utils::getConfig("PROVIDER");
		switch ($this->provider) {
			case DatabaseManager::MYSQL_PROVIDER:
				$databaseConfig = Utils::getConfig("MYSQL_database");
				$this->db = [$databaseConfig['host'], $databaseConfig['user'], $databaseConfig['pass'], $databaseConfig['name']];
				break;

			case DatabaseManager::SQLITE_PROVIDER:
				$this->db = Utils::getConfig("SQLITE_database")["name"];
				break;
		}
	}

	public function onRun(): void {
		$provider = $this->provider;
		$db = (array) $this->db;
		switch ($provider) {
			case DatabaseManager::MYSQL_PROVIDER:
				$db = new PDO(
					"mysql:host=" . $db[0] . ";dbname=" . $db[3],
					$db[1],
					$db[2]
				);
				break;
			case DatabaseManager::SQLITE_PROVIDER:
				$db = new PDO("sqlite:" . $db[0] . ".sqlite");
				break;
			default:
				$db = new PDO("sqlite:" . $db[0] . ".sqlite");
				break;
		}
		$query = $db->prepare($this->query);
		$query->execute((array) $this->args);
		$results = "";
		if ($this->class !== null) {
			$query->setFetchMode(PDO::FETCH_CLASS, $this->class);
			$results = $query->fetchAll();
		}
		$this->setResult($results);
	}

	public function onCompletion(): void {
		call_user_func($this->callable, $this->getResult());
	}
}