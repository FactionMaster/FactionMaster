<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;

class HomeTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "fhome";
    const SLUG = "fhome";

    public function init(): self {
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT , 
            `name` VARCHAR(255) NOT NULL , 
            `faction` VARCHAR(255) NOT NULL , 
            `x` INT(11) NOT NULL,
            `y` INT(11) NOT NULL,
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