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

namespace ShockedPlot7560\FactionMaster\Command;

use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
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
use ShockedPlot7560\FactionMaster\Command\Subcommand\PlaceScoreboard;
use ShockedPlot7560\FactionMaster\Command\Subcommand\RemoveFlagCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SethomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SettingsCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SynchroCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\UnclaimCommand;
use ShockedPlot7560\FactionMaster\Route\MainRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionCommand extends BaseCommand {

    protected function prepare(): void {
        $this->registerSubCommand(new FactionCreateCommand("create", Utils::getText("", "COMMAND_CREATE_DESCRIPTION")));
        $this->registerSubCommand(new FactionTopCommand("top", Utils::getText("", "COMMAND_TOP_DESCRIPTION")));
        $this->registerSubCommand(new FactionManageCommand("manage", Utils::getText("", "COMMAND_MANAGE_DESCRIPTION")));
        $this->registerSubCommand(new ClaimCommand("claim", Utils::getText("", "COMMAND_CLAIM_DESCRIPTION")));
        $this->registerSubCommand(new UnclaimCommand("unclaim", Utils::getText("", "COMMAND_UNCLAIM_DESCRIPTION")));
        $this->registerSubCommand(new SethomeCommand("sethome", Utils::getText("", "COMMAND_SETHOME_DESCRIPTION")));
        $this->registerSubCommand(new DelhomeCommand("delhome", Utils::getText("", "COMMAND_DELHOME_DESCRIPTION")));
        $this->registerSubCommand(new HomeTpCommand("tp", Utils::getText("", "COMMAND_TP_DESCRIPTION")));
        $this->registerSubCommand(new HomeCommand("home", Utils::getText("", "COMMAND_HOME_DESCRIPTION")));
        $this->registerSubCommand(new MapCommand("map", Utils::getText("", "COMMAND_MAP_DESCRIPTION")));
        $this->registerSubCommand(new HelpCommand("help", Utils::getText("", "COMMAND_HELP_DESCRIPTION")));
        $this->registerSubCommand(new InfoCommand("info", Utils::getText("", "COMMAND_INFO_DESCRIPTION")));
        $this->registerSubCommand(new ClaimInfoCommand("claiminfo", Utils::getText("", "COMMAND_CLAIM_INFO_DESCRIPTION")));
        $this->registerSubCommand(new ExtensionCommand("extension", Utils::getText("", "COMMAND_EXTENSION_DESCRIPTION")));
        $this->registerSubCommand(new PlaceScoreboard("scoreboard", "Place scoreboard"));
        $this->registerSubCommand(new AddFlagCommand("addflag", Utils::getText("", "COMMAND_ADD_FLAG")));
        $this->registerSubCommand(new RemoveFlagCommand("removeflag", Utils::getText("", "COMMAND_REMOVE_FLAG")));
        $this->registerSubCommand(new SynchroCommand("synchro", Utils::getText("", "COMMAND_SYNCHRO")));
        $this->registerSubCommand(new SettingsCommand("settings", "Give all the FactionMaster settings"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }
        if (count($args) == 0) {
            Utils::processMenu(RouterFactory::get(MainRoute::SLUG), $sender->getPlayer());
            return;
        }
    }
}