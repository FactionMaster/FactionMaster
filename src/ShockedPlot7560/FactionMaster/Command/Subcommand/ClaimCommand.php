<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Utils\Ids;
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
        if ((isset($permissions[Ids::PERMISSION_ADD_CLAIM]) && $permissions[Ids::PERMISSION_ADD_CLAIM]) || $UserEntity->rank == Ids::OWNER_ID) {
            $Player = $sender->getPlayer();
            $Chunk = $Player->getLevel()->getChunkAtPosition($Player);
            $X = $Chunk->getX();
            $Z = $Chunk->getZ();
            $World = $Player->getLevel()->getName();

            $FactionClaim = MainAPI::getFactionClaim($World, $X, $Z);
            if ($FactionClaim === null) {
                $FactionPlayer = MainAPI::getFactionOfPlayer($sender->getName());
                if (count(MainAPI::getClaimsFaction($UserEntity->faction)) < $FactionPlayer->max_claim) {
                    if (MainAPI::addClaim($sender->getPlayer(), $UserEntity->faction)) {
                        $sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_CLAIM"));
                        return;
                    }else{
                        $sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
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