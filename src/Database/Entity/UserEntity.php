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

class UserEntity extends EntityDatabase {
	use FactionUtils;

	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getName()
	 * @var string
	 */
	public $name;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getFactionName(), getFactionEntity()
	 * @var string|null
	 */
	public $faction;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getRank()
	 * @var int|null
	 */
	public $rank;
	/**
	 * DO NOT USE THIS CONSTANT
	 * @see getLanguage()
	 * @var string
	 */
	public $language;

	public function setName(string $name): void {
		$this->name = $name;
	}

	public function setRank(?int $rank): void {
		$this->rank = $rank;
	}

	public function setLanguage(string $slug): void {
		$this->language = $slug;
	}

	public function getName(): string {
		return $this->name;
	}

	public function getRank(): ?int {
		return $this->rank;
	}

	public function getLanguage(): string {
		return $this->language;
	}
}