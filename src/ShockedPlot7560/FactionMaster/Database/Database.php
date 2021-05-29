<?php

namespace ShockedPlot7560\FactionMaster\Database;

use PDO;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Table\ClaimTable;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\TableInterface;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;

class Database {

    /** @var \PDO */
    private $PDO;

    /** @var \ShockedPlot7560\FactionMaster\Database\Table\TableInterface[] */
    private $tables;

    public function __construct(Main $Main) {
        $databaseConfig = $Main->config->get("MYSQL_database");
        $db = new PDO(
            "mysql:host=" . $databaseConfig['host'] . ";dbname=" . $databaseConfig['name'], 
            $databaseConfig['user'], 
            $databaseConfig['pass']
        );
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
            new HomeTable($this->getPDO())
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