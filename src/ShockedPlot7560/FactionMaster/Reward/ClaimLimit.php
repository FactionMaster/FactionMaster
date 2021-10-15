<?php

declare(strict_types=1);

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

namespace ShockedPlot7560\FactionMaster\Reward;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class ClaimLimit extends Reward implements RewardInterface {
	public function __construct($value = 0) {
		$this->value = $value;
		$this->nameSlug = "REWARD_CLAIM_LIMIT_NAME";
		$this->type = RewardType::CLAIM_LIMIT;
	}

	public function executeGet(string $factionName, $value = null): bool {
		if ($value !== null) {
			$this->setValue($value);
		}

		$result = MainAPI::updateFactionOption($factionName, 'max_claim', $this->getValue());
		return $result === false ? false : true;
	}

	public function executeCost(string $factionName, $value = null) {
		if ($value !== null) {
			$this->setValue($value);
		}

		return MainAPI::updateFactionOption($factionName, 'max_claim', $this->getValue() * -1) === false ? "ERROR" : true;
	}
}