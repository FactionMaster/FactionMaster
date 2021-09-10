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

use DateTime;
use ShockedPlot7560\FactionMaster\API\MainAPI;

class FactionEntity extends EntityDatabase {

    /** @var string */
    protected $name;
    /** @var array|string|null */
    protected $members;
    /** @var int */
    protected $visibility;
    /** @var int */
    protected $xp;
    /** @var int */
    protected $level;
    /** @var string */
    protected $description;
    /** @var string */
    protected $messageFaction;
    /** @var array|string */
    protected $ally;
    /** @var int */
    protected $max_player;
    /** @var int */
    protected $max_ally;
    /** @var int */
    protected $max_claim;
    /** @var int */
    protected $max_home;
    /** @var int */
    protected $power;
    /** @var string|array */
    protected $permissions;
    /** @var string */
    protected $date;

    public function __construct() {
        if (isset($this->members) && $this->members !== null && is_string($this->members)) {
            $this->members = unserialize(base64_decode($this->members));
        }
        if (isset($this->ally) && is_string($this->ally)) {
            $this->ally = unserialize(base64_decode($this->ally));
        }
        if (isset($this->permissions) && is_string($this->permissions)) {
            $this->permissions = unserialize(base64_decode($this->permissions));
        }
    }

    public function getName(): string {
        return $this->name;
    }

    /**
     * @return array|string|null
     */
    public function getMembers() {
        return $this->members;
    }

    public function getVisibilityId(): int {
        return $this->visibility;
    }

    public function getXP(): int {
        return $this->xp;
    }

    public function getLevel(): int {
        return $this->level;
    }

    public function getDescription(): string {
        return $this->description;
    }

    public function getMessage(): string {
        return $this->messageFaction;
    }

    public function getAlly(): array {
        return $this->ally;
    }

    public function getMaxPlayer(): int {
        return $this->max_player;
    }

    public function getMaxAlly(): int {
        return $this->max_ally;
    }

    public function haveMaxAlly(): bool {
        return count($this->getAlly()) >= $this->getMaxAlly();
    }

    public function getMaxClaim(): int {
        return $this->max_claim;
    }

    public function getMaxHome(): int {
        return $this->max_home;
    }

    public function getPower(): int {
        return $this->power;
    }

    public function getPermissions(): array {
        return $this->permissions;
    }

    public function getDateString(): string {
        return $this->date;
    }

    public function getDate(): DateTime {
        return new DateTime($this->getDateString());
    }

    /**
     * @return FactionEntity[]
     */
    public function getAllyInstance(): array {
        $array = [];
        foreach ($this->ally as $key => $Ally) {
            $array[] = MainAPI::getFaction($Ally);
        }
        return $array;
    }
}