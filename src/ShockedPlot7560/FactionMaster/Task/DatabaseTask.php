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
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DatabaseTask extends AsyncTask {

    private $provider;
    private $db;
    private $query;
    private $args;
    private $callable;

    public function __construct(string $query, array $args, ?callable $callable = null) {
        $this->query = $query;
        $this->args = $args;
        $this->callable = $callable;
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

        switch ($provider) {
            case Database::MYSQL_PROVIDER:
                $db = new PDO(
                    "mysql:host=" . $this->db[0] . ";dbname=" . $this->db[3], 
                    $this->db[1], 
                    $this->db[2]
                );
                break;
            case Database::SQLITE_PROVIDER:
                $db = new PDO("sqlite:".$this->db[3].".sqlite");
                break;
        }
        $query = $db->prepare($this->query);
        $query->execute((array) $this->args);
        $this->setResult("");
    }

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server): void {
        call_user_func($this->callable);
    }
}