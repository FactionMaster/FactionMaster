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

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Event\FactionClaimEvent;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Reward\RewardFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ClaimCommand extends BaseSubCommand {

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        $permissions = MainAPI::getMemberPermission($sender->getName());
        $UserEntity = MainAPI::getUser($sender->getName());
        if ($permissions === null) {
            $sender->sendMessage(Utils::getText($sender->getName(), "NEED_FACTION"));
            return;
        }
        if (Utils::haveAccess($permissions, $UserEntity, PermissionIds::PERMISSION_ADD_CLAIM)) {
            $Player = $sender->getPlayer();
            $Chunk = $Player->getLevel()->getChunkAtPosition($Player);
            $X = $Chunk->getX();
            $Z = $Chunk->getZ();
            $World = $Player->getLevel()->getName();

            $FactionClaim = MainAPI::getFactionClaim($World, $X, $Z);
            if ($FactionClaim === null) {
                $FactionPlayer = MainAPI::getFactionOfPlayer($sender->getName());
                $Claims = MainAPI::getClaimsFaction($UserEntity->faction);
                if (count($Claims) < $FactionPlayer->max_claim) {
                    $claimCost = Main::getInstance()->config->get("claim-cost");
                    $ItemCost = RewardFactory::get($claimCost['type']);
                    switch (Main::getInstance()->config->get("claim-provider")) {
                        case 'flat':
                            $ItemCost->setValue($claimCost["value"]);
                            break;
                        case 'addition':
                            $ItemCost->setValue($claimCost["value"] * (\count($Claims) + 1));
                            break;
                        case 'multiplicative':
                            $ItemCost->setValue($claimCost["value"] * (Main::getInstance()->config->get('multiplication-factor')**count($Claims)));
                            break;
                        case 'decrease':
                            $ItemCost->setValue($claimCost["value"] - (Main::getInstance()->config->get('decrease-factor') * count($Claims)));
                            break;
                    }
                    if (($result = $ItemCost->executeCost($FactionPlayer->name)) === true) {
                        if (MainAPI::addClaim($sender->getPlayer(), $UserEntity->faction)) {
                            (new FactionClaimEvent($sender, $FactionPlayer, $Chunk, $ItemCost->getType(), $ItemCost->getValue()))->call();
                            $sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_CLAIM"));
                            return;
                        }else{
                            $sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
                            return;
                        }
                    }else{
                        \var_dump($result);
                        $sender->sendMessage(Utils::getText($sender->getName(), $result));
                        return;
                    }
                }else{
                    $sender->sendMessage(Utils::getText($sender->getName(), "MAX_CLAIM_REACH"));
                    return;
                }
            }else{
                $sender->sendMessage(Utils::getText($sender->getName(), "ALREADY_CLAIM"));
                return;
            }
        }else{
            $sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
            return;
        }
    }

}