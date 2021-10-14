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
use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\args\RawStringArgument;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class PlaceScoreboard extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission("factionmaster.scoreboard.place");
        $this->registerArgument(0, new RawStringArgument("slug"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if ($sender instanceof Player) {
            if ($sender->hasPermission("factionmaster.scoreboard.place")) {
                if (!in_array($args["slug"], array_keys(LeaderboardManager::$queryList))) {
                    $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_SCOREBOARD_INVALID_SLUG", ["list" => implode(",", array_keys(LeaderboardManager::$queryList))]));
                    return;
                }
                $position = $sender->getPosition();
                $coord = join("|", [
                    $position->getX(),
                    $position->getY(),
                    $position->getZ(),
                    $position->getWorld()->getDisplayName()
                ]);
                LeaderboardManager::closeLeaderboard($args["slug"]);
                LeaderboardManager::placeScoreboard($args["slug"], $coord);
                $config = ConfigManager::getLeaderboardConfig();
                $config->set("leaderboards", [
                    [
                        "slug" => "faction",
                        "position" => $coord,
                        "active" => true
                    ]
                ]);
                $config->save();
                $sender->sendMessage(Utils::getText("", "COMMAND_SCOREBOARD_SUCCESS"));
            }else{
                $sender->sendMessage(Utils::getText("", "DONT_PERMISSION"));
            }
        }
    }

}