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

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\BaseSubCommand;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;

class AddFlagCommand extends BaseSubCommand {
	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("areaName"));
		$this->registerArgument(1, new RawStringArgument("type"));
		$this->setPermission("factionmaster.flag.add");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if ($sender instanceof Player) {
			if (count($args) > 0) {
				if ($sender->hasPermission("factionmaster.flag.add")) {
					$player = $sender->getPlayer();
					$chunk = $player->getLevel()->getChunkAtPosition($player);
					$x = $chunk->getX();
					$z = $chunk->getZ();
					$world = $player->getLevel()->getName();
					if (MainAPI::getFactionClaim($world, $x, $z) !== null) {
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
					MainAPI::addClaim($sender->getPlayer(), $args["areaName"], $flag);
					Utils::newMenuSendTask(new MenuSendTask(
						function () use ($world, $x, $z) {
							return MainAPI::getFactionClaim($world, $x, $z) instanceof ClaimEntity;
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