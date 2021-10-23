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
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Event\FactionUnclaimEvent;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\BaseSubCommand;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class UnclaimCommand extends BaseSubCommand {
	protected function prepare(): void {
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		$player = $sender->getPlayer();
		$chunk = $player->getLevel()->getChunkAtPosition($player);
		$x = $chunk->getX();
		$z = $chunk->getZ();
		$world = $player->getLevel()->getName();
		$factionClaim = MainAPI::getFactionClaim($world, $x, $z);
		$userEntity = MainAPI::getUser($sender->getName());
		if ($factionClaim === null) {
			$sender->sendMessage(Utils::getText($sender->getName(), "NOT_CLAIMED"));
			return;
		} elseif ($factionClaim->getFactionName() === $userEntity->getFactionName()) {
			$permissions = MainAPI::getMemberPermission($sender->getName());
			if (Utils::haveAccess($permissions, $userEntity, PermissionIds::PERMISSION_REMOVE_CLAIM)) {
				MainAPI::removeClaim($sender->getPlayer(), $userEntity->getFactionName());
				Utils::newMenuSendTask(new MenuSendTask(
					function () use ($world, $x, $z) {
						return !MainAPI::getFactionClaim($world, $x, $z) instanceof ClaimEntity;
					},
					function () use ($player, $factionClaim, $chunk, $sender) {
						(new FactionUnclaimEvent($player, $factionClaim, $chunk))->call();
						$sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_UNCLAIM"));
					},
					function () use ($sender) {
						$sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
					}
				));
				return;
			} else {
				$sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
				return;
			}
		}
	}
}