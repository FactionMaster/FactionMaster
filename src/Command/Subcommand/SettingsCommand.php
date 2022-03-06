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
use ShockedPlot7560\FactionMaster\Manager\ImageManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class SettingsCommand extends FactionSubCommand {
	public function getId(): string {
		return "COMMAND_SETTINGS_DESCRIPTION";
	}

	public function prepare(): void {
		$this->setPermission("factionmaster.settings");
	}

	public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
		$sender->sendMessage("§2FactionMaster settings:");
		$sender->sendMessage("  §8>> §7Database Provider: " . Utils::getConfig("PROVIDER"));
		$sender->sendMessage("  §8>> §7Default home limit: §a" . Utils::getConfig("default-home-limit"));
		$sender->sendMessage("  §8>> §7Default claim limit: §a" . Utils::getConfig("default-claim-limit"));
		$sender->sendMessage("  §8>> §7Default member limit: §a" . Utils::getConfig("default-player-limit"));
		$sender->sendMessage("  §8>> §7Default ally limit: §a" . Utils::getConfig("default-ally-limit"));
		$sender->sendMessage("  §8>> §7Default visibility: §a" . Utils::getConfig("default-faction-visibility"));
		$sender->sendMessage("  §8>> §7Default xp: §a" . Utils::getConfig("default-faction-xp"));
		$sender->sendMessage("  §8>> §7Default power: §a" . Utils::getConfig("default-power"));
		$sender->sendMessage("  §8>> §7Default level: §a" . Utils::getConfig("default-faction-level"));
		$sender->sendMessage("  §8>> §7Default description: §a" . (Utils::getConfig("default-faction-description") === "" ? "none" : Utils::getConfig("default-faction-description")));
		$sender->sendMessage("  §8>> §7Default message: §a" . (Utils::getConfig("default-faction-message") === "" ? "none" : Utils::getConfig("default-faction-message")));
		$map = Utils::getConfig("f-map-task");
		$sender->sendMessage("  §8>> §7F map task: " . ($map === false ? "§cno" : "§ayes"));
		$sender->sendMessage("  §8>> §7Claim alert: " . (Utils::getConfig("message-alert") === true ? "§ayes" : "§cno"));
		$sender->sendMessage("  §8>> §7Image button: " . (ImageManager::isImageEnable() === true ? "§ayes" : "§cno"));
		$sender->sendMessage("  §8>> §7Faction chat: " . (Utils::getConfig("faction-chat-active") === true ? "§ayes" : "§cno"));
		$sender->sendMessage("  §8>> §7Alliance chat: " . (Utils::getConfig("ally-chat-active") === true ? "§ayes" : "§cno"));
		$sender->sendMessage("§7Broadcast: ");
		$sender->sendMessage("§8>> §7Faction create: " . (Utils::getConfig("broadcast-faction-create") === true ? "§ayes" : "§cno"));
		$sender->sendMessage("§8>> §7Faction delete: " . (Utils::getConfig("broadcast-faction-delete") === true ? "§ayes" : "§cno"));
		$sender->sendMessage("§8>> §7Faction transferProperty: " . (Utils::getConfig("broadcast-faction-transferProperty") === true ? "§ayes" : "§cno"));
	}
}