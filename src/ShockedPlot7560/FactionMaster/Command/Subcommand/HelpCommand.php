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

    protected function prepare(): void {
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        $sender->sendMessage("§8=§7=§8=§7=§8=§7=§8=§7=§8=§7= §bFactionMaster command §8=§7=§8=§7=§8=§7=§8=§7=§8=§7=");
        $sender->sendMessage(" §8>> §r§b/f: §7" . Utils::getText($sender->getName(), "COMMAND_FACTION_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f top: §7" . Utils::getText($sender->getName(), "COMMAND_TOP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f manage: §7" . Utils::getText($sender->getName(), "COMMAND_MANAGE_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f claim: §7" . Utils::getText($sender->getName(), "COMMAND_CLAIM_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f unclaim: §7" . Utils::getText($sender->getName(), "COMMAND_UNCLAIM_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f sethome <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_SETHOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f delhome <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_DELHOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f tp <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_TP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f home: §7" . Utils::getText($sender->getName(), "COMMAND_HOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f create: §7" . Utils::getText($sender->getName(), "COMMAND_CREATE_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f map: §7" . Utils::getText($sender->getName(), "COMMAND_MAP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f help: §7" . Utils::getText($sender->getName(), "COMMAND_HELP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f info <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_INFO_DESCRIPTION_GLOBAL"));
        $sender->sendMessage(" §8>> §r§b/f claiminfo: §7" . Utils::getText($sender->getName(), "COMMAND_CLAIM_INFO_DESCRIPTION"));
    }

}