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

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class SethomeCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("name", false));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        if (!isset($args["name"])) {
            $this->sendUsage();
            return;
        }
        $permissions = MainAPI::getMemberPermission($sender->getName());
        $UserEntity = MainAPI::getUser($sender->getName());
        if ($permissions === null) {
            $sender->sendMessage(Utils::getText($sender->getName(), "NEED_FACTION"));
            return;
        }
        if ((isset($permissions[Ids::PERMISSION_ADD_FACTION_HOME]) && $permissions[Ids::PERMISSION_ADD_FACTION_HOME]) || $UserEntity->rank == Ids::OWNER_ID) {
            $Player = $sender->getPlayer();
            if (!MainAPI::getFactionHome($UserEntity->faction, $args["name"])) {
                $Faction = MainAPI::getFaction($UserEntity->faction);
                if (count(MainAPI::getFactionHomes($UserEntity->faction)) < $Faction->max_home) {
                    $Chunk = $Player->getLevel()->getChunkAtPosition($Player);
                    if (Main::getInstance()->config->get("allow-home-ennemy-claim") && MainAPI::getFactionClaim($Player->getLevel()->getName(), $Chunk->getX(), $Chunk->getZ())  === null) {
                        if (MainAPI::addHome($Player, $UserEntity->faction, $args['name'])) {
                            $sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_HOME_CREATE"));
                            return;
                        }else{
                            $sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
                            return;
                        }
                    }else{
                        $sender->sendMessage(Utils::getText($sender->getName(), "CANT_SETHOME_ENNEMY_CLAIM"));
                        return;
                    }
                }else{
                    $sender->sendMessage(Utils::getText($sender->getName(), "MAX_HOME_REACH"));
                    return;
                }
            }else{
                $sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_HOME_NAME"));
                return;
            }
        }else{
            $sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
            return;
        }
    }

}