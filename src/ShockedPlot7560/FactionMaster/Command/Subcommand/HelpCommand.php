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

use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HelpCommand extends BaseSubCommand {

    private $player;

    protected function prepare(): void {
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $this->player = $sender;
        $sender->sendMessage(Utils::getConfig("help-command-header"));
        $sender->sendMessage($this->getString("/f", "COMMAND_FACTION_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f top", "COMMAND_TOP_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f manage", "COMMAND_MANAGE_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f claim", "COMMAND_CLAIM_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f unclaim", "COMMAND_UNCLAIM_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f sethome <:name>", "COMMAND_SETHOME_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f delhome <:name>", "COMMAND_DELHOME_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f tp <:name>", "COMMAND_TP_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f home", "COMMAND_HOME_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f create", "COMMAND_CREATE_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f map [on|off]", "COMMAND_MAP_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f help", "COMMAND_HELP_DESCRIPTION"));
        $sender->sendMessage($this->getString("/f info <:name>", "COMMAND_INFO_DESCRIPTION_GLOBAL"));
        $sender->sendMessage($this->getString("/f claiminfo", "COMMAND_CLAIM_INFO_DESCRIPTION"));
        if ($sender->hasPermission("factionmaster.flag.add")) {
            $sender->sendMessage($this->getString("/f addflag <areaName> <type>", "COMMAND_ADD_FLAG"));
        }
        if ($sender->hasPermission("factionmaster.flag.remove")) {
            $sender->sendMessage($this->getString("/f removeflag", "COMMAND_REMOVE_FLAG"));
        }
        if ($sender->hasPermission("factionmaster.extension.list")) {
            $sender->sendMessage($this->getString("/f extension", "COMMAND_EXTENSION_DESCRIPTION"));
        }
        if ($sender->hasPermission("factionmaster.leaderboard.place")) {
            $sender->sendMessage($this->getString("/f leaderboard", "COMMAND_SCOREBOARD_DESCRIPTION"));
        }
        if ($sender->hasPermission("factionmaster.synchro.launch")) {
            $sender->sendMessage($this->getString("/f synchro", "COMMAND_SYNCHRO"));
        }
    }

    protected function getString(string $usage, string $translationSlug): string {
        $patern = Utils::getConfig("help-command-lign");
        return str_replace(
            ["{command}", "{description}"],
            [$usage, Utils::getText($this->player->getName(), $translationSlug)],
            $patern
        );
    }

}