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
use pocketmine\level\Level;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Manager\ConfigManager;
use ShockedPlot7560\FactionMaster\Manager\LeaderboardManager;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class PlaceScoreboard extends BaseSubCommand {

    protected function prepare(): void {
        $this->setPermission("factionmaster.scoreboard.place");
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if ($sender instanceof Player) {
            if ($sender->hasPermission("factionmaster.scoreboard.place")) {
                $position = $sender->getPosition();
<<<<<<< HEAD
                $coordinates = explode("|", Utils::getConfig("faction-scoreboard-position"));
                if (count($coordinates) == 4) {
                    $level = Main::getInstance()->getServer()->getLevelByName($coordinates[3]);
                    if (!$level instanceof Level) {
                        $sender->sendMessage(" §c§l>> The world name given in the config.yml seems to be incorrect, world unknown or not found. Please update it correctly.");
                        $sender->sendMessage(" §c>> §4Actual coordonate : §c{x:" . $sender->getX() . ", y: " . $sender->getY() . ", z: " . $sender->getZ() . "}");
                        $sender->sendMessage(" §c>> §4Actual worldName : §c" . $sender->getLevel()->getName());
                        $sender->sendMessage(" §c>> §cData to add in config.yml : §c§l" . join("|", [$sender->getX(), $sender->getY(), $sender->getZ(), $sender->getLevel()->getName()]));
                        return;
                    }
                    $entities = Main::getInstance()->getServer()->getLevelByName($coordinates[3])->getEntities();
                    foreach ($entities as $entity) {
                        if ($entity instanceof ScoreboardEntity) {
                            $entity->flagForDespawn();
                            $entity->despawnFromAll();
                        }
                    }
                }
                Main::placeScoreboard();
=======
>>>>>>> dev
                $coord = join("|", [
                    $position->getX(),
                    $position->getY(),
                    $position->getZ(),
                    $position->getLevel()->getName()
                ]);
                $config = ConfigManager::getLeaderboardConfig();
                $config->set("position", $coord);
                $config->set("enabled", true);
                $config->save();
                $sender->sendMessage(Utils::getText("", "COMMAND_SCOREBOARD_SUCCESS"));
            }else{
                $sender->sendMessage(Utils::getText("", "DONT_PERMISSION"));
            }
        }
    }

}