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
use function array_search;
use function count;
use function in_array;
use function is_string;
use function json_decode;

class FactionEntity extends EntityDatabase {

	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getName()
	 * @var string
	 */
	public $name;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMembers()
	 * @var array|string|null
	 */
	public $members;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getVisibilityId()
	 * @var int
	 */
	public $visibility;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getXP()
	 * @var int
	 */
	public $xp;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getLevel()
	 * @var int
	 */
	public $level;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getDescription()
	 * @var string
	 */
	public $description;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMessage()
	 * @var string
	 */
	public $message;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getAlly(), getAllyInstance()
	 * @var array|string
	 */
	public $ally;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMaxPlayer()
	 * @var int
	 */
	public $maxPlayer;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMaxAlly()
	 * @var int
	 */
	public $maxAlly;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMaxClaim()
	 * @var int
	 */
	public $maxClaim;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getMaxHome()
	 * @var int
	 */
	public $maxHome;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getPower()
	 * @var int
	 */
	public $power;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getPermissions()
	 * @var array|string
	 */
	public $permissions;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getDate(), getDateString()
	 * @var string
	 */
	public $date;

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function setMembers(array $members): void {
		$this->members = $members;
	}

	public function removeMember(string $memberName): void {
		if (isset($this->getMembers()[$memberName])) {
			unset($this->members[$memberName]);
		}
	}

	public function addMember(string $memberName, int $rank): void {
		$this->members[$memberName] = $rank;
	}

	public function setMemberRank(string $memberName, int $rank): void {
		if (isset($this->getMembers()[$memberName])) {
			$this->members[$memberName] = $rank;
		}
	}

	public function setVisibility(int $visibility): void {
		$this->visibility = $visibility;
	}

	public function setXp(int $xp): void {
		$this->xp = $xp;
	}

	public function setLevel(int $level): void {
		$this->level = $level;
	}

	public function setDescription(string $description): void {
		$this->description = $description;
	}

	public function setMessage(string $message): void {
		$this->message = $message;
	}

	public function setAlly(array $ally): void {
		$this->ally = $ally;
	}

	public function addAlly(string $factionName): void {
		if (!in_array($factionName, $this->getAlly(), true)) {
			$this->ally[] = $factionName;
		}
	}

	public function removeAlly(string $factionName): void {
		if (in_array($factionName, $this->getAlly(), true)) {
			unset($this->ally[array_search($factionName, $this->getAlly(), true)]);
		}
	}

	public function setMaxPlayer(int $max): void {
		$this->maxPlayer = $max;
	}

	public function setMaxAlly(int $max): void {
		$this->maxAlly = $max;
	}

	public function setMaxClaim(int $max): void {
		$this->maxClaim = $max;
	}

	public function setMaxHome(int $max): void {
		$this->maxHome = $max;
	}

	public function setPower(int $power): void {
		$this->power = $power;
	}

	public function addPower(int $power): void {
		$this->power += $power;
	}

	public function removePower(int $power): void {
		$this->addPower($power * -1);
	}

	public function setPermissions(array $permissions): void {
		$this->permissions = $permissions;
	}

	public function setPermission(int $rank, array $permission): void {
		$this->permissions[$rank] = $permission;
	}

	public function setDate(string $date): void {
		$this->date = $date;
	}

	public function setDatetime(DateTime $date): void {
		$this->setDate($date->format("Y-m-d H:i:s"));
	}

	public function __construct() {
		if (isset($this->members) && $this->members !== null && is_string($this->members)) {
			$this->members = json_decode($this->members, true);
		}
		if (isset($this->ally) && is_string($this->ally)) {
			$this->ally = json_decode($this->ally, true);
		}
		if (isset($this->permissions) && is_string($this->permissions)) {
			$this->permissions = json_decode($this->permissions, true);
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
		return $this->description ?? "";
	}

	public function getMessage(): string {
		return $this->message ?? "";
	}

	public function getAlly(): array {
		return $this->ally;
	}

	public function getMaxPlayer(): int {
		return $this->maxPlayer;
	}

	public function getMaxAlly(): int {
		return $this->maxAlly;
	}

	public function haveMaxAlly(): bool {
		return count($this->getAlly()) >= $this->getMaxAlly();
	}

	public function getMaxClaim(): int {
		return $this->maxClaim;
	}

	public function getMaxHome(): int {
		return $this->maxHome;
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
		foreach ($this->getAlly() as $key => $ally) {
			$array[] = MainAPI::getFaction($ally);
		}
		return $array;
	}

	public function isAlly(string $allyName): bool {
		return in_array($allyName, $this->getAlly(), true);
	}
}