<?php

declare(strict_types=1);

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

use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\QueryBuildeur;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function call_user_func;
use function in_array;
use function strlen;
use function substr;
use function version_compare;

class MigrationManager {

	/** @var callable[] */
	private static $list = [];
	private static $configDbToCheck = [];
	/** @var Main */
	private static $main;

	public static function init(Main $main) {
		self::$main = $main;
		$config = new Config(Utils::getDataFolder() . "config.yml", Config::YAML);
		self::$list = [
			"2.1.2-alpha" => function () {},
			"2.1.3-alpha" => function () {},
			"2.1.4-alpha" => function () {},
			"2.2.0" => function () {},
			"2.3.0" => function () {},
			"2.3.1" => function () {},
			"3.0.0" => function () {
				self::$main->getLogger()->critical("FactionMaster 3.0.0 was not compatible with the anterior version, please remake all the installation before launch the server");
				self::$main->getLogger()->critical("For precaution, factionMaster will bbe disabled now");
				self::$main->getServer()->getPluginManager()->disablePlugin(self::$main);
				return false;
			},
			"3.0.1" => function () {},
			"3.0.2" => function () {},
			"3.0.3" => function () {
				self::$main->getLogger()->notice("This version includes a new translation: turkish, if you use the images with the buttons, please update the texture pack, available here : ");
				self::$main->getLogger()->notice("https://github.com/FactionMaster/FactionMaster/tree/stable/resource_pack");
			},
			"3.1.2" => function () {
				$leaderborardConfig = ConfigManager::getLeaderboardConfig();
				$leaderborardConfig->set("leaderboards", [
					[
						"slug" => "faction",
						"position" => $leaderborardConfig->get("position"),
						"active" => $leaderborardConfig->get("enabled")
					]
				]);
				$leaderborardConfig->__unset("position");
				$leaderborardConfig->__unset("enabled");
				$leaderborardConfig->save();
				self::$main->getLogger()->notice("Update your old leaderboard.yml format");
				self::$main->getLogger()->notice("New resource pack are available, thanks to xAliTura01, you can download it here : https://github.com/FactionMaster/FactionMaster/tree/stable/resource_pack");
			}
		];
		self::$configDbToCheck = [
			[
				"CONFIG_INST" => new Config(Utils::getDataFolder() . "translation.yml", Config::YAML),
				"CONFIG_NAME" => "default-language",
				"TABLE_NAME" => UserTable::TABLE_NAME,
				"COLUMN_NAME" => "language",
				"TABLE_CLASS" => UserTable::class
			]
		];
	}

	public static function addConfigDbToCheck(array $check): void {
		self::$configDbToCheck[] = $check;
	}

	public static function migrate(string $version) {
		$actualVersion = "";
		foreach (self::$list as $versionName => $callable) {
			if (version_compare($version, $versionName, "<")) {
				$actualVersion = $versionName;
				self::$main->getLogger()->debug("Starting migration from $versionName");
				if (call_user_func($callable) === false) {
					return;
				}
				self::$main->getLogger()->debug("Migration from $versionName finish");
			}
		}
		$config = ConfigManager::getVersionConfig();
		$config->set("migrate-version", $actualVersion);
		$config->save();
	}

	public static function updateConfigDb(): void {
		$pdo = DatabaseManager::getPDO();
		$provider = Utils::getConfig("PROVIDER");
		switch ($provider) {
			case DatabaseManager::MYSQL_PROVIDER:
				foreach (self::$configDbToCheck as $configDB) {
					$configValue = $configDB["CONFIG_INST"]->get($configDB["CONFIG_NAME"]);
					$query = $pdo->prepare("SHOW COLUMNS FROM " . $configDB["TABLE_NAME"]);
					$query->execute();
					foreach ($query->fetchAll() as $columnData) {
						if ($columnData["Field"] === $configDB["COLUMN_NAME"]) {
							if ($configValue != $columnData["Default"]) {
								self::$main->getLogger()->notice("Changing the configuration of '" . $configDB["CONFIG_NAME"] . "' detected, change the value for $configValue");
								$query = $pdo->prepare("ALTER TABLE " . $configDB["TABLE_NAME"] . " ALTER COLUMN " . $configDB["COLUMN_NAME"] . " SET DEFAULT '" . $configValue . "'");
								$query->execute();
							}
						}
					}
				}
				break;
			default:
				$exploretable = [];
				foreach (self::$configDbToCheck as $configDB) {
					if (in_array($configDB["TABLE_NAME"], $exploretable, true)) {
						continue;
					}
					$exploretable[] = $configDB["TABLE_NAME"];
					$configValue = $configDB["CONFIG_INST"]->get($configDB["CONFIG_NAME"]);
					$query = $pdo->prepare("PRAGMA table_info(" . $configDB["TABLE_NAME"] . ")");
					$query->execute();
					foreach ($query->fetchAll() as $columnData) {
						$columnData["dflt_value"] = is_null($columnData["dflt_value"]) ? "" : $columnData["dflt_value"];
						$dflt_value = substr($columnData["dflt_value"], 1, strlen($columnData["dflt_value"]) - 2);
						foreach (self::$configDbToCheck as $conf) {
							$value = $conf["CONFIG_INST"]->get($conf["CONFIG_NAME"]);
							if ($columnData["name"] == $conf["COLUMN_NAME"] && $dflt_value != $value) {
								self::$main->getLogger()->notice("Changing the configuration of '" . $conf["CONFIG_NAME"] . "' detected, change the value for $value");
							}
						}
						if ($columnData["name"] == $configDB["COLUMN_NAME"]) {
							if ($dflt_value != $configValue) {
								$query = $pdo->prepare("SELECT * FROM " . $configDB["TABLE_NAME"]);
								$query->execute();
								$result = $query->fetchAll();
								$query = $pdo->prepare("DROP TABLE " . $configDB["TABLE_NAME"]);
								$query->execute();
								(new $configDB["TABLE_CLASS"]($pdo))->init();
								foreach ($result as $lign) {
									$query = $pdo->prepare("INSERT INTO " . $configDB["TABLE_NAME"] . " (" . QueryBuildeur::buildInsert($lign) . ") VALUES (" . QueryBuildeur::buildInsert($lign, QueryBuildeur::PREPARE_INSERT_MODE) . ")");
									$query->execute($lign);
								}
							}
						}
					}
				}
				break;
		}
	}
}