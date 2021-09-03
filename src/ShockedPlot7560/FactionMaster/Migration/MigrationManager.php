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

namespace ShockedPlot7560\FactionMaster\Migration;

use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MigrationManager {

    private static $list = [];
    private static $configDbToCheck = [];

    public static function init() {
        self::$list = [
            "2.1.2-alpha" => function () {},
            "2.1.3-alpha" => function () {},
        ];
        $config = new Config(Main::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        self::$configDbToCheck = [
            [
                "CONFIG_INST" => $config,
                "CONFIG_NAME" => "default-home-limit",
                "TABLE_NAME" => FactionTable::TABLE_NAME,
                "COLUMN_NAME" => "max_home",
                "TABLE_CLASS" => FactionTable::class
            ], [
                "CONFIG_INST" => $config,
                "CONFIG_NAME" => "default-claim-limit",
                "TABLE_NAME" => FactionTable::TABLE_NAME,
                "COLUMN_NAME" => "max_claim",
                "TABLE_CLASS" => FactionTable::class
            ], [
                "CONFIG_INST" => $config,
                "CONFIG_NAME" => "default-member-limit",
                "TABLE_NAME" => FactionTable::TABLE_NAME,
                "COLUMN_NAME" => "max_player",
                "TABLE_CLASS" => FactionTable::class
            ], [
                "CONFIG_INST" => $config,
                "CONFIG_NAME" => "default-ally-limit",
                "TABLE_NAME" => FactionTable::TABLE_NAME,
                "COLUMN_NAME" => "max_ally",
                "TABLE_CLASS" => FactionTable::class
            ], [
                "CONFIG_INST" => $config,
                "CONFIG_NAME" => "default-power",
                "TABLE_NAME" => FactionTable::TABLE_NAME,
                "COLUMN_NAME" => "power",
                "TABLE_CLASS" => FactionTable::class
            ], [
                "CONFIG_INST" => new Config(Main::getInstance()->getDataFolder() . "translation.yml", Config::YAML),
                "CONFIG_NAME" => "default-language",
                "TABLE_NAME" => UserTable::TABLE_NAME,
                "COLUMN_NAME" => "language",
                "TABLE_CLASS" => UserTable::class
            ]
        ];
    }

    public static function migrate(string $version) {
        $actualVersion = "";
        foreach (self::$list as $versionName => $callable) {
            if (version_compare($version, $versionName, "<")) {
                $actualVersion = $versionName;
                Main::getInstance()->getLogger()->debug("Starting migration from $versionName");
                call_user_func($callable);
                Main::getInstance()->getLogger()->debug("Migration from $versionName finish");
            }
        }
        Main::getInstance()->version->set("migrate-version", $actualVersion);
        Main::getInstance()->version->save();
    }

    public static function updateConfigDb(): void {
        $pdo = MainAPI::$PDO;
        $provider = Utils::getConfig("PROVIDER");
        switch ($provider) {
            case Database::MYSQL_PROVIDER:
                foreach (self::$configDbToCheck as $configDB) {
                    $configValue = $configDB["CONFIG_INST"]->get($configDB["CONFIG_NAME"]);
                    $query = $pdo->prepare("SHOW COLUMNS FROM " . $configDB["TABLE_NAME"]);
                    $query->execute();
                    foreach ($query->fetchAll() as $columnData) {
                        if ($columnData["Field"] === $configDB["COLUMN_NAME"]) {
                            if ($configValue != $columnData["Default"]) {
                                Main::getInstance()->getLogger()->notice("Changing the configuration of '" . $configDB["CONFIG_NAME"] . "' detected, change the value for $configValue");
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
                    if (in_array($configDB["TABLE_NAME"], $exploretable)) continue;
                    $exploretable[] = $configDB["TABLE_NAME"];
                    $configValue = $configDB["CONFIG_INST"]->get($configDB["CONFIG_NAME"]);
                    $query = $pdo->prepare("PRAGMA table_info(" . $configDB["TABLE_NAME"] . ")");
                    $query->execute();
                    foreach ($query->fetchAll() as $columnData) {
                        $dflt_value = substr($columnData["dflt_value"], 1, strlen($columnData["dflt_value"]) - 2);
                        foreach (self::$configDbToCheck as $conf) {
                            $value = $conf["CONFIG_INST"]->get($conf["CONFIG_NAME"]);
                            if ($columnData["name"] == $conf["COLUMN_NAME"] && $dflt_value != $value) {
                                Main::getInstance()->getLogger()->notice("Changing the configuration of '" . $conf["CONFIG_NAME"] . "' detected, change the value for $value");
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