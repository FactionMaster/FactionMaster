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

class BankHistoryTable implements TableInterface {

    /** @var \PDO */
    private $PDO;

    const TABLE_NAME = "bank_history";
    const SLUG = "bank_history";

    public function init(): self {
        $auto_increment = Main::getInstance()->config->get("PROVIDER") === Database::MYSQL_PROVIDER ? "AUTO_INCREMENT" : "AUTOINCREMENT";
        $this->PDO->query("CREATE TABLE IF NOT EXISTS `". self::TABLE_NAME ."` ( 
            `id` INTEGER NOT NULL PRIMARY KEY $auto_increment , 
            `faction` VARCHAR(255) NOT NULL , 
            `entity` VARCHAR(255) NOT NULL, 
            `amount` INT(11) NOT NULL,
            `type` INT(1) NOT NULL,
            `date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
        )");
        return $this;
    }

    public function __construct(PDO $PDO) {
        $this->PDO = $PDO;
    }

}