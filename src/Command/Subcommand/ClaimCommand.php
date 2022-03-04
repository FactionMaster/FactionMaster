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
use pocketmine\world\format\Chunk;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Event\FactionClaimEvent;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function floor;

class ClaimCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_CLAIM_DESCRIPTION";
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$permissions = MainAPI::getMemberPermission($sender->getName());
		$userEntity = MainAPI::getUser($sender->getName());
		if ($permissions === null) {
			$sender->sendMessage(Utils::getText($sender->getName(), "NEED_FACTION"));
			return;
		}
		if (Utils::haveAccess($permissions, $userEntity, PermissionIds::PERMISSION_ADD_CLAIM)) {
			$player = $sender;
			$x = floor($player->getPosition()->getFloorX()/16);
			$z = floor($player->getPosition()->getFloorZ()/16);
			$chunk = $player->getWorld()->getChunk($x, $z);
			if (!$chunk instanceof Chunk) {
				return;
			}
			$world = $player->getWorld()->getDisplayName();

			$factionClaim = MainAPI::getFactionClaim($world, $x, $z);
			if ($factionClaim === null) {
				$factionPlayer = MainAPI::getFactionOfPlayer($sender->getName());
				$claims = MainAPI::getClaimsFaction($userEntity->getFactionName());
				if (count($claims) < $factionPlayer->getMaxClaim()) {
					$claimCost = ConfigManager::getConfig()->get("claim-cost");
					$itemCost = RewardFactory::get($claimCost['type']);
					switch (ConfigManager::getConfig()->get("claim-provider")) {
						case 'flat':
							$itemCost->setValue($claimCost["value"]);
							break;
						case 'addition':
							$itemCost->setValue($claimCost["value"] * (count($claims) + 1));
							break;
						case 'multiplicative':
							$itemCost->setValue($claimCost["value"] * (ConfigManager::getConfig()->get('multiplication-factor') ** count($claims)));
							break;
						case 'decrease':
							$itemCost->setValue($claimCost["value"] - (ConfigManager::getConfig()->get('decrease-factor') * count($claims)));
							break;
					}
					if (($result = $itemCost->executeCost($factionPlayer->getName())) === true) {
						MainAPI::addClaim($sender, $userEntity->getFactionName());
						Utils::newMenuSendTask(new MenuSendTask(
							function () use ($world, $x, $z) {
								return MainAPI::isClaim($world, $x, $z);
							},
							function () use ($sender, $factionPlayer, $chunk, $itemCost) {
								$event = new FactionClaimEvent($sender, $factionPlayer, $chunk, $itemCost);
								$event->call();
								$sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_CLAIM"));
							},
							function () use ($sender) {
								$sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
							}
						));
						return;
					} else {
						$sender->sendMessage(Utils::getText($sender->getName(), $result));
						return;
					}
				} else {
					$sender->sendMessage(Utils::getText($sender->getName(), "MAX_CLAIM_REACH"));
					return;
				}
			} else {
				if ($factionClaim->getFlag() !== null) {
					switch ($factionClaim->getFlag()) {
						case Ids::FLAG_SPAWN:
							$sender->sendMessage(Utils::getText($sender->getName(), "SPAWN_INFO"));
							break;

						case Ids::FLAG_WARZONE:
							$sender->sendMessage(Utils::getText($sender->getName(), "WARZONE_INFO"));
							break;
					}
				} else {
					$sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_CLAIM"));
				}
				return;
			}
		} else {
			$sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
			return;
		}
	}
}