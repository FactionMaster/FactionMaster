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
use ShockedPlot7560\FactionMaster\Command\Argument\EnumArgument;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;

class AddFlagCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_ADD_FLAG";
	}

	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("areaName"));
		$this->registerArgument(1, new EnumArgument("type", ["warzone", "spawn"]));
		$this->setPermission("factionmaster.flag.add");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if ($sender instanceof Player) {
			if (count($args) > 0) {
				if ($sender->hasPermission("factionmaster.flag.add")) {
					$player = $sender;
					$chunkX = $player->getPosition()->getFloorX() >> 4;
					$chunkZ = $player->getPosition()->getFloorZ() >> 4;
					$chunk = $player->getWorld()->getChunk($chunkX, $chunkZ);
					$world = $player->getWorld()->getDisplayName();
					if (MainAPI::getFactionClaim($world, $chunkX, $chunkZ) !== null) {
						$sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_CLAIM"));
						return;
					}
					switch ($args["type"]) {
						case 'warzone':
						case 'wz':
							$flag = Ids::FLAG_WARZONE;
							break;
						case 'spawn':
						case 'spwn':
							$flag = Ids::FLAG_SPAWN;
							break;
						default:
							$sender->sendMessage(Utils::getText($sender->getName(), "FLAG_ADD_COMMAND"));
							return;
					}
					MainAPI::addClaim($sender, $args["areaName"], $flag);
					Utils::newMenuSendTask(new MenuSendTask(
						function () use ($world, $chunkX, $chunkZ) {
							return MainAPI::getFactionClaim($world, $chunkX, $chunkZ) instanceof ClaimEntity;
						},
						function () use ($sender) {
							$sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_ADD_FLAG"));
						},
						function () use ($sender) {
							$sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
						}
					));
				} else {
					$sender->sendMessage(Utils::getText("", "DONT_PERMISSION"));
				}
			} else {
				$this->sendUsage();
			}
		}
	}
}