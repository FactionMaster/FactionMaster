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
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function floor;

class RemoveFlagCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_REMOVE_FLAG";
	}

	protected function prepare(): void {
		$this->setPermission("factionmaster.flag.remove");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if ($sender instanceof Player) {
			if ($sender->hasPermission("factionmaster.flag.remove")) {
				$player = $sender;
				$z = floor($player->getPosition()->getFloorX()/16);
				$x = floor($player->getPosition()->getFloorZ()/16);
				$chunk = $player->getWorld()->getChunk($x, $z);
				$world = $player->getWorld()->getDisplayName();
				$factionClaim = MainAPI::getFactionClaim($world, $x, $z);
				if ($factionClaim === null) {
					$sender->sendMessage(Utils::getText($sender->getName(), "NOT_CLAIMED"));
					return;
				} elseif ($factionClaim->getFlag() !== null) {
					MainAPI::removeClaim($sender, $factionClaim->getFactionName());
					Utils::newMenuSendTask(new MenuSendTask(
						function () use ($world, $x, $z) {
							return !MainAPI::getFactionClaim($world, $x, $z) instanceof ClaimEntity;
						},
						function () use ($sender) {
							$sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_REMOVE_FLAG"));
						},
						function () use ($sender) {
							$sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
						}
					));
					return;
				}
			} else {
				$sender->sendMessage(Utils::getText("", "DONT_PERMISSION"));
			}
		}
	}
}