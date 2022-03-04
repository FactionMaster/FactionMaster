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

namespace ShockedPlot7560\FactionMaster\Manager;

use ShockedPlot7560\FactionMaster\Command\Subcommand\AddFlagCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\ClaimCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\ClaimInfoCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\DelhomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\ExtensionCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionCreateCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionManageCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionTopCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\HelpCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\HomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\HomeTpCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\InfoCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\MapCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\PlaceLeaderboard;
use ShockedPlot7560\FactionMaster\Command\Subcommand\RemoveFlagCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\RemoveNearLeaderboardCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SethomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SettingsCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SynchroCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\UnclaimCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionSubCommand;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class CommandManager {

	/** @var FactionSubCommand[] */
	private static $commands = [];

	public static function init(bool $light = false): void {
		if (!$light) {
			self::registerCommand(new FactionCreateCommand("create"));
			self::registerCommand(new FactionManageCommand("manage"));
			self::registerCommand(new ClaimCommand("claim"));
			self::registerCommand(new UnclaimCommand("unclaim"));
			self::registerCommand(new SethomeCommand("sethome"));
			self::registerCommand(new DelhomeCommand("delhome"));
			self::registerCommand(new HomeTpCommand("tp"));
			self::registerCommand(new HomeCommand("home"));
			self::registerCommand(new MapCommand("map"));
			self::registerCommand(new InfoCommand("info"));
			self::registerCommand(new ClaimInfoCommand("claiminfo"));
			self::registerCommand(new FactionTopCommand("top"));
		}
		self::registerCommand(new HelpCommand("help"));
		self::registerCommand(new ExtensionCommand("extension"));
		self::registerCommand(new PlaceLeaderboard("placeleaderboard"));
		self::registerCommand(new RemoveNearLeaderboardCommand("removeleaderboard"));
		self::registerCommand(new AddFlagCommand("addflag"));
		self::registerCommand(new RemoveFlagCommand("removeflag"));
		self::registerCommand(new SynchroCommand("synchro"));
		self::registerCommand(new SettingsCommand("settings"));
	}

	public static function registerCommand(FactionSubCommand $command): void {
		self::$commands[$command->getName()] = $command;
	}

	public static function disableCommand(string $name): void {
		if (isset(self::$commands[$name])) {
			unset(self::$commands[$name]);
		}
	}

	public static function isCommandEnable(string $name): bool {
		return isset(self::$commands[$name]);
	}

	/** @return FactionSubCommand[] */
	public static function getCommands(): array {
		return self::$commands;
	}

	/** @return FactionSubCommand[] */
	public static function getAll(): array {
		return self::$commands;
	}
}