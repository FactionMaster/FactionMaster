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

namespace ShockedPlot7560\FactionMaster\Database\Table;

use PDO;
use ShockedPlot7560\FactionMaster\Database\Database;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;

class FactionTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "faction";
    const SLUG = "faction";

    public function init(): self {
        $auto_increment = Main::getInstance()->config->get("PROVIDER") === Database::MYSQL_PROVIDER ? "AUTO_INCREMENT" : "AUTOINCREMENT";
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INTEGER NOT NULL PRIMARY KEY $auto_increment, 
            `name` VARCHAR(22) NOT NULL, 
            `members` VARCHAR(255) NOT NULL DEFAULT '". \base64_encode(\serialize([]))."',
            `visibility` INT(11) DEFAULT " . Ids::PRIVATE_VISIBILITY . ",
            `xp` INT(11) NOT NULL DEFAULT '0',
            `level` INT(11) NOT NULL DEFAULT '1',
            `description` TEXT, 
            `messageFaction` TEXT,
            `ally` VARCHAR(255) NOT NULL DEFAULT '". \base64_encode(\serialize([]))."',
            `max_player` INT(11) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-player-limit") . "',
            `max_ally` INT(11) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-ally-limit") . "',
            `max_claim` INT(11) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-claim-limit") . "',
            `max_home` INT(11) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-home-limit") . "',
            `power` INT(11) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-power") . "',
            `money` INT(16) NOT NULL DEFAULT '". Main::getInstance()->config->get("default-money") . "',
            `permissions` TEXT,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        return $this;
    }

    public function __construct(PDO $PDO) {
        $this->PDO = $PDO;
    }

}