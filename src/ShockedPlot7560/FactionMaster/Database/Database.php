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

namespace ShockedPlot7560\FactionMaster\Database;

use PDO;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Table\BankHistoryTable;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\TableInterface;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;

class Database {

    const MYSQL_PROVIDER = "MYSQL";
    const SQLITE_PROVIDER  = "SQLITE";

    /** @var \PDO */
    private $PDO;

    /** @var \ShockedPlot7560\FactionMaster\Database\Table\TableInterface[] */
    private $tables;

    public function __construct(Main $Main) {
        $PROVIDER = $Main->config->get('PROVIDER');
        switch ($PROVIDER) {
            case self::MYSQL_PROVIDER:
                $databaseConfig = $Main->config->get("MYSQL_database");
                $db = new PDO(
                    "mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['name'], 
                    $databaseConfig['user'], 
                    $databaseConfig['pass']
                );
                break;
            
            case self::SQLITE_PROVIDER:
                $databaseConfig = $Main->config->get("SQLITE_database");
                $db = new PDO("sqlite:".$databaseConfig['name'].".sqlite");
                break;
                break;
            default:
                $Main::$logger->alert("Please give a valid PROVIDER in config.yml, use only : " . self::MYSQL_PROVIDER . " or " . self::SQLITE_PROVIDER);
                $Main->getServer()->getPluginManager()->disablePlugin($Main);
                return;
                break;
        }
        
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->PDO = $db;

        MainAPI::init($db);
        $this->initTable();
    }

    public function getPDO() : PDO {
        return $this->PDO;
    }

    private function initTable() : void {
        $tables = [
            new FactionTable($this->getPDO()),
            new UserTable($this->getPDO()),
            new InvitationTable($this->getPDO()),
            new ClaimTable($this->getPDO()),
            new HomeTable($this->getPDO()),
            new BankHistoryTable($this->getPDO())
        ];
        foreach ($tables as $key => $table) {
            $table = $table->init();
            $this->tables[$table::SLUG] = $table;
        }
    }

    public function getTables() : array {
        return $this->tables;
    }

    public function getTable(string $slug) : ?TableInterface {
        return $this->getTables()[$slug] ?? null;
    }

}