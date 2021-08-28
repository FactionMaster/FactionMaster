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

namespace ShockedPlot7560\FactionMaster\Database\Entity;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class FactionEntity {

    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var array|string|null */
    public $members;
    /** @var int */
    public $visibility;
    /** @var int */
    public $xp;
    /** @var int */
    public $level;
    /** @var string|null */
    public $description;
    /** @var string|null */
    public $messageFaction;
    /** @var array|string */
    public $ally;
    /** @var int */
    public $max_player;
    /** @var int */
    public $max_ally;
    /** @var int */
    public $max_claim;
    /** @var int */
    public $max_home;
    /** @var int */
    public $power;
    /** @var string|array */
    public $permissions;
    /** @var string */
    public $date;

    public function __construct() {
        if (isset($this->members) && $this->members !== null && is_string($this->members)){
            $this->members = unserialize(\base64_decode($this->members));
        }
        if (isset($this->ally) && is_string($this->ally)){
            $this->ally = unserialize(\base64_decode($this->ally));
        }
        if (isset($this->permissions) && is_string($this->permissions)){
            $this->permissions = unserialize(\base64_decode($this->permissions));
        }
    }

    public function getAllyInstance() : array {
        $array = [];
        foreach ($this->ally as $key => $Ally) {
            $array[] = MainAPI::getFaction($Ally);
        }
        return $array;
    }
}