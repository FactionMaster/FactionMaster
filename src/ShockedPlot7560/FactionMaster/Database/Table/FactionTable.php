<?php

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;
use ShockedPlot7560\FactionMaster\Utils\Ids;

class FactionTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "faction";
    const SLUG = "faction";

    public function init(): self {
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INT(11) NOT NULL AUTO_INCREMENT, 
            `name` VARCHAR(22) NOT NULL, 
            `members` VARCHAR(255) NOT NULL DEFAULT '". \base64_encode(\serialize([]))."',
            `visibility` INT(11) DEFAULT " . Ids::PRIVATE_VISIBILITY . ",
            `xp` INT(11) NOT NULL DEFAULT '0',
            `level` INT(11) NOT NULL DEFAULT '1',
            `description` TEXT, 
            `messageFaction` TEXT,
            `ally` VARCHAR(255) NOT NULL DEFAULT '". \base64_encode(\serialize([]))."',
            `max_player` INT(11) NOT NULL DEFAULT 2,
            `max_ally` INT(11) NOT NULL DEFAULT 2,
            `max_claim` INT(11) NOT NULL DEFAULT 2,
            `max_home` INT(11) NOT NULL DEFAULT 2,
            `power` INT(11) NOT NULL DEFAULT 0,
            `money` INT(16) NOT NULL DEFAULT 0,
            `permissions` TEXT,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`), 
            UNIQUE (`name`)
        ) ENGINE = MyISAM");
        return $this;
    }

    public function __construct(PDO $PDO) {
        $this->PDO = $PDO;
    }

}