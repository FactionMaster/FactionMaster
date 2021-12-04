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

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\player\Player;
use pocketmine\world\format\Chunk;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Reward\RewardInterface;

class FactionClaimEvent extends FactionEvent implements Forcable {
	use PlayerEvent;

	protected $player;
	private $chunk;
	private $costReward;

	/**
	 * @param string|FactionEntity $faction
	 */
	public function __construct(Player $player, $faction, Chunk $chunk, RewardInterface $costReward, bool $isForce = false) {
		parent::__construct($faction, $isForce);
		$this->player = $player;
		$this->chunk = $chunk;
		$this->costReward = $costReward;
	}

	public function getChunk(): Chunk {
		return $this->chunk;
	}

	public function getCostReward(): RewardInterface {
		return $this->costReward;
	}
}