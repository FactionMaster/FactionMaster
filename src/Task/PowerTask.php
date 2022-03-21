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

namespace ShockedPlot7560\FactionMaster\Task;

use pocketmine\scheduler\Task;
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Utils\Ids;

use function in_array;

class PowerTask extends Task {
	public function onRun(): void {
		$scannedFaction = [];
		$config = ConfigManager::getConfig();
		foreach (Server::getInstance()->getOnlinePlayers() as $player) {
			if ($config->get("online-leader", false) == true) {
				$user = MainAPI::getUser($player->getName());
				if ($user->getRank() !== Ids::OWNER_ID) {
					continue;
				}
				$fac = $user->getFactionEntity();
				if ($user instanceof UserEntity && $fac instanceof FactionEntity) {
					MainAPI::changePower($fac->getName(), $config->get("power-staying-online-number", 1));
					continue;
				}
			} else {
				$fac = MainAPI::getFactionOfPlayer($player->getName());
				if ($fac instanceof FactionEntity && !in_array($fac->getName(), $scannedFaction, true)) {
					MainAPI::changePower($fac->getName(), $config->get("power-staying-online-number", 1));
					$scannedFaction[] = $fac->getName();
				}
			}
		}
	}
}