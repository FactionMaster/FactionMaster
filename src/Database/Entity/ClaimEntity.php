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

use pocketmine\math\Vector2;
use pocketmine\math\Vector3;
use pocketmine\world\World;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ClaimEntity extends EntityDatabase {
	use FactionUtils;
	use ServerIp;

	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getFactionName(), getFactionEntity()
	 * @var string
	 */
	public $faction;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getX()
	 * @var int
	 */
	public $x;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getZ()
	 * @var int
	 */
	public $z;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getLevel(), getLevelName()
	 * @var string
	 */
	public $world;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getServerIp()
	 * @var string
	 */
	public $server;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getFlag()
	 * @var int|null
	 */
	public $flag;

	public function setX(int $x): void {
		$this->x = $x;
	}

	public function setZ(int $z): void {
		$this->z = $z;
	}

	public function setLevelName(string $levelName): void {
		$this->world = $levelName;
	}

	public function setFlag(?int $flag): void {
		$this->flag = $flag;
	}

	/**
	 * @param Vector2|Vector3 $vector
	 */
	public function setVector($vector): void {
		if ($vector instanceof Vector2) {
			$this->setX($vector->getX());
			$this->setZ($vector->getY());
		} elseif ($vector instanceof Vector3) {
			$this->setX($vector->getX());
			$this->setZ($vector->getZ());
		}
	}

	public function getFactionName(): string {
		return $this->faction;
	}

	public function getFactionEntity(): ?FactionEntity {
		if ($this->getFlag() === null) {
			if ($this->getFactionName() === "") {
				return null;
			}
			return MainAPI::getFaction($this->getFactionName());
		} else {
			return null;
		}
	}

	public function getX(): int {
		return $this->x;
	}

	public function getZ(): int {
		return $this->z;
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

	public function getFlag(): ?int {
		return $this->flags;
	}

	public function toString(): string {
		return Utils::claimToString($this->getX(), $this->getZ(), $this->getLevelName());
	}

	public function __toString() {
		return $this->toString();
	}

	public function getVector(): Vector2 {
		return new Vector2($this->getX(), $this->getZ());
	}
}