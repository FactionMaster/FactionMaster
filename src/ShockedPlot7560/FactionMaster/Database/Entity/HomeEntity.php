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

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeEntity extends EntityDatabase {

    use FactionUtils;

    /** @var string */
    protected $faction;
    /** @var string */
    protected $name;
    /** @var int */
    protected $x;
    /** @var int */
    protected $y;
    /** @var int */
    protected $z;
    /** @var string */
    protected $world;

    public function getName(): string {
        return $this->name;
    }

    public function getLevelName(): string {
        return $this->world;
    }

    public function getLevel(): ?Level {
        return Main::getInstance()->getServer()->getLevelByName($this->getLevelName());
    }

    public function toString(): string {
        return Utils::homeToString($this->x, $this->y, $this->z, $this->world);
    }

    public function __toString(): string{
        return $this->toString();
    }

    public function toArray(): array {
        return Utils::homeToArray($this->x, $this->y, $this->z, $this->world);
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