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

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function floor;

class ClaimInfoCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_CLAIM_INFO_DESCRIPTION";
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$player = $sender;
		$x = floor($player->getPosition()->getFloorX()/16);
		$z = floor($player->getPosition()->getFloorZ()/16);
		$chunk = $player->getWorld()->getChunk($x, $z);
		$world = $player->getWorld()->getDisplayName();

		$factionClaim = MainAPI::getFactionClaim($world, $x, $z);
		if ($factionClaim !== null) {
			if ($factionClaim->getFlag() === null) {
				Main::getInstance()->getServer()->dispatchCommand($player, "f info " . $factionClaim->getFactionName());
			} else {
				switch ($factionClaim->getFlag()) {
					case Ids::FLAG_SPAWN:
						$sender->sendMessage(Utils::getText($sender->getName(), "SPAWN_INFO"));
						break;

					case Ids::FLAG_WARZONE:
						$sender->sendMessage(Utils::getText($sender->getName(), "WARZONE_INFO"));
						break;
				}
			}
			return;
		} else {
			$sender->sendMessage(Utils::getText($sender->getName(), "NOT_CLAIMED"));
			return;
		}
	}
}