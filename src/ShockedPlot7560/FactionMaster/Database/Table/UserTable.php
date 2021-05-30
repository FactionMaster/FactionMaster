<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class UserTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "user";
    const SLUG = "user";

    public function init() : self
    {
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `name` VARCHAR(22) NOT NULL, 
            `faction` VARCHAR(255) DEFAULT NULL,
            `rank` INT(11) DEFAULT NULL,
            `language` VARCHAR(255) NOT NULL DEFAULT '". Utils::getConfigLang("default-language") ."',
            PRIMARY KEY (`name`), 
            UNIQUE (`name`)
        ) ENGINE = MyISAM");
        return $this;
    }

    public function __construct(PDO $PDO)
    {
        $this->PDO = $PDO;
    }
}