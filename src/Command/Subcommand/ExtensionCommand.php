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
use pocketmine\permission\DefaultPermissions;
use ShockedPlot7560\FactionMaster\Manager\ExtensionManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ExtensionCommand extends FactionSubCommand {

	public function getId(): string {
		return "COMMAND_EXTENSION_DESCRIPTION";
	}

	protected function prepare(): void {
		$this->setPermission("factionmaster.extension.list");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
			$sender->sendMessage(Utils::getText($sender->getName(), "NO_PERMISSION"));
			return;
		}
		$extensions = ExtensionManager::getExtensions();
		$string = "";
		foreach ($extensions as $extension) {
			$string .= "§a" . $extension->getExtensionName() . "§f, ";
		}
		if ($string !== "") {
			$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_EXTENSION_BASE", ["extensionList" => $string]));
		} else {
			$sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_EXTENSION_NO"));
		}
	}
}