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
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Event\FactionHomeTpEvent;
use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeTpCommand extends BaseSubCommand {
	protected function prepare(): void {
		$this->registerArgument(0, new RawStringArgument("name", false));
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender instanceof Player) {
			return;
		}

		if (!isset($args['name'])) {
			$this->sendUsage();
			return;
		}
		$permissions = MainAPI::getMemberPermission($sender->getName());
		$userEntity = MainAPI::getUser($sender->getName());
		if (Utils::haveAccess($permissions, $userEntity, PermissionIds::PERMISSION_TP_FACTION_HOME)) {
			$home = MainAPI::getFactionHome($userEntity->getFactionName(), $args["name"]);
			if ($home !== null) {
				$sender->teleport(new Vector3($home->getX(), $home->getY(), $home->getZ()));
				(new FactionHomeTpEvent($sender, $userEntity->getFactionName(), $args['name'], $home))->call();
				$sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_HOME_TELEPORT"));
			} else {
				$sender->sendMessage(Utils::getText($sender->getName(), "HOME_DONT_EXIST"));
			}
		} else {
			$sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
		}
	}
}