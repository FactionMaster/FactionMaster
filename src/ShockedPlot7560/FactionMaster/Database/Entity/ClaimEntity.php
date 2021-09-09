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
use pocketmine\math\Vector2;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ClaimEntity extends EntityDatabase {

    use FactionUtils;
    use ServerIp;

    /** @var string */
    protected $faction;
    /** @var string */
    protected $x;
    /** @var string */
    protected $z;
    /** @var string */
    protected $level;
    /** @var string */
    protected $server;
    /** @var int|null */
    protected $flag;

    public function getFactionName(): string {
        if ($this->getFlag() === null) {
            return $this->faction;
        } else {
            // TODO: implements flags
        }
    }

    public function getFactionEntity(): ?FactionEntity {
        if ($this->getFlag() === null) {
            if ($this->getFactionName() === "") return null;
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
        return $this->level;
    }

    public function getLevel(): ?Level {
        if (!$this->isActive()) return null;
        return Main::getInstance()->getServer()->getLevelByName($this->getLevelName());
    }

    public function getFlag(): ?int {
        return $this->flag;
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