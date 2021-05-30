<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;

class InvitationTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "invitation";
    const SLUG = "invitation";

    public function init() : self
    {
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT, 
            `sender` VARCHAR(255) NOT NULL, 
            `receiver` VARCHAR(255) NOT NULL,
            `type` VARCHAR(255) NOT NULL,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`)
        ) ENGINE = MyISAM");
        return $this;
    }

    public function __construct(PDO $PDO)
    {
        $this->PDO = $PDO;
    }

}