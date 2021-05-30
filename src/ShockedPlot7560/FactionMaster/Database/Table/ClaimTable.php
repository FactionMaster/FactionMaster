<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;

class ClaimTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "claim";
    const SLUG = "claim";

    public function init(): self {
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT , 
            `faction` VARCHAR(255) NOT NULL , 
            `x` INT(11) NOT NULL , 
            `z` INT(11) NOT NULL,
            `world` VARCHAR(255) NOT NULL,
            PRIMARY KEY (`id`)
        ) ENGINE = MyISAM");
        return $this;
    }

    public function __construct(PDO $PDO) {
        $this->PDO = $PDO;
    }

}