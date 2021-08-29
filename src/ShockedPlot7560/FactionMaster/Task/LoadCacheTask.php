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
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\HomeEntity;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use Throwable;

class LoadCacheTask extends AsyncTask {

    private $provider;
    private $db;

    public function __construct() {
        $this->provider = Utils::getConfig("PROVIDER");
        switch ($this->provider) {
            case Database::MYSQL_PROVIDER:
                $databaseConfig = Utils::getConfig("MYSQL_database");
                $this->db = array($databaseConfig['host'], $databaseConfig['user'], $databaseConfig['pass'], $databaseConfig['name']);
                break;
            
            case Database::SQLITE_PROVIDER:
                $this->db = array(Main::getInstance()->getDataFolder() . Utils::getConfig("SQLITE_database")["name"] . ".db");
                break;
        }
    }

    public function onRun(): void {
        $provider = $this->provider;
        $results["home"] = [];
        $results["claim"] = [];

        switch ($provider) {
            case Database::MYSQL_PROVIDER:
                try {
                    $db = new \MySQLi($this->db[0], $this->db[1], $this->db[2], $this->db[3]);
                    $query = $db->query("SELECT * FROM " . ClaimTable::TABLE_NAME);
                    while ($resultArr = $query->fetch_object(ClaimEntity::class)) {
                        $results["claim"][$resultArr->faction] = [$resultArr->getToString()];
                    }
                    $query = $db->query("SELECT * FROM " . HomeTable::TABLE_NAME);
                    while ($resultArr = $query->fetch_object(HomeEntity::class)) {
                        $results["home"][$resultArr->faction] = [$resultArr->name => $resultArr->getToArray()];
                    }
                } catch (Throwable $Exception) {
                    $results["home"] = [];
                    $results["claim"] = [];
                }                        
                break;
            case Database::SQLITE_PROVIDER:
                try {
                    $db = new \SQLite3($this->db[0]);
                    $query = $db->query("SELECT * FROM " . ClaimTable::TABLE_NAME);
                    while ($resultArr = $query->fetchArray(SQLITE3_ASSOC)) {
                        $results["claim"][$resultArr["faction"]] = [Utils::claimToString($resultArr["x"], $resultArr["z"], $resultArr["world"])];
                    }
                    $query = $db->query("SELECT * FROM " . HomeTable::TABLE_NAME);
                    while ($resultArr = $query->fetchArray(SQLITE3_ASSOC)) {
                        $results["home"][$resultArr["faction"]] = [$resultArr["name"] => Utils::homeToArray($resultArr["x"], $resultArr["y"], $resultArr["z"], $resultArr["world"])];
                    }
                } catch (Throwable $Exception) {
                    $results["home"] = [];
                    $results["claim"] = [];
                }     
                break;
        }
        $this->setResult($results);
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server): void {
        $result = $this->getResult();
        if (!empty($result)) {
            foreach ($result["claim"] as $faction => $claim) {
                if (!isset(MainAPI::$claim[$faction])) {
                    MainAPI::$claim[$faction] = $claim;
                }else{
                    MainAPI::$claim[$faction][] = $claim;
                }
            }      
            foreach ($result["home"] as $faction => $home) {
                if (!isset(MainAPI::$home[$faction])) {
                    MainAPI::$home[$faction] = $home;
                }else{
                    MainAPI::$home[$faction][array_key_first($home)] = [array_key_first($home) => $home[0]];
                }
            }  
        }
    }
}