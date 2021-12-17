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

use pocketmine\math\Vector3;
use pocketmine\world\Position;
use pocketmine\world\World;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeEntity extends EntityDatabase {
	use FactionUtils;
	use ServerIp;

	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getFactionName(), getfactionEntity
	 * @var string
	 */
	public $faction;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getName()
	 * @var string
	 */
	public $name;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getX()
	 * @var int
	 */
	public $x;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getY()
	 * @var int
	 */
	public $y;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getZ()
	 * @var int
	 */
	public $z;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getLevelName(), getLevel()
	 * @var string
	 */
	public $world;

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function setX(int $x): void {
		$this->x = $x;
	}

	public function setY(int $y): void {
		$this->y = $y;
	}

	public function setZ(int $z): void {
		$this->z = $z;
	}

	public function setWorldName(string $worldName): void {
		$this->world = $worldName;
	}

	public function setVector(Vector3 $vector): void {
		$this->setX($vector->getX());
		$this->setY($vector->getY());
		$this->setX($vector->getX());
	}

	public function getName(): string {
		return $this->name;
	}

	public function getLevelName(): string {
		return $this->world;
	}

	public function getLevel(): ?World {
		if (!$this->isActive()) {
			return null;
		}
		return Main::getInstance()->getServer()->getWorldManager()->getWorldByName($this->getLevelName());
	}

	public function toString(): string {
		return Utils::homeToString($this->getX(), $this->getY(), $this->getZ(), $this->getLevelName());
	}

	public function __toString(): string {
		return $this->toString();
	}

	public function toArray(): array {
		return Utils::homeToArray($this->getX(), $this->getY(), $this->getZ(), $this->getLevelName());
	}

	public function getX(): int {
		return $this->x;
	}

	public function getY(): int {
		return $this->y;
	}

	public function getZ(): int {
		return $this->z;
	}

	public function getVector(): Vector3 {
		return new Vector3($this->getX(), $this->getY(), $this->getZ());
	}

	public function getPosition(): Position {
		return new Position($this->getX(), $this->getY(), $this->getZ(), $this->getLevel());
	}
}