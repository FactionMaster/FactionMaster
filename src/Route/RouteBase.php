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
namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;

abstract class RouteBase implements Route, RouteSlug {

	/** @var Collection|null */
	protected $collection;
	/** @var UserEntity */
	protected $userEntity;
	/** @var array */
	protected $userPermissions;
	/** @var Player */
	protected $player;
	/** @var null|array */
	protected $params;

	public function getCollection(): ?Collection {
		return $this->collection;
	}

	protected function setCollection(Collection $collection): void {
		$this->collection = $collection;
	}

	public function getUserEntity(): UserEntity {
		return $this->userEntity;
	}

	public function getUserPermissions(): array {
		return $this->userPermissions;
	}

	public function getPlayer(): Player {
		return $this->player;
	}

	public function getParams(): ?array {
		return $this->params;
	}

	protected function getFaction(): ?FactionEntity {
		return $this->getUserEntity()->getFactionEntity();
	}

	protected function init(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
		$this->player = $player;
		$this->userEntity = $userEntity;
		$this->userPermissions = $userPermissions;
		$this->params = $params;
	}
}