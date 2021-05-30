<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class UnclaimCommand extends BaseSubCommand {

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        $Player = $sender->getPlayer();
        $Chunk = $Player->getLevel()->getChunkAtPosition($Player);
        $X = $Chunk->getX();
        $Z = $Chunk->getZ();
        $World = $Player->getLevel()->getName();
        $factionClaim = MainAPI::getFactionClaim($World, $X, $Z);
        $UserEntity = MainAPI::getUser($sender->getName());
        if ($factionClaim === null) {
            $sender->sendMessage(Utils::getText($sender->getName(), "NOT_CLAIMED"));
            return;
        }elseif ($factionClaim === $UserEntity->faction) {
            $permissions = MainAPI::getMemberPermission($sender->getName());
            if ((isset($permissions[Ids::PERMISSION_REMOVE_CLAIM]) && $permissions[Ids::PERMISSION_REMOVE_CLAIM]) || $UserEntity->rank == Ids::OWNER_ID) {
                if (MainAPI::removeClaim($sender->getPlayer(), $UserEntity->faction)) {
                    $sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_UNCLAIM"));
                    return;
                }else{
                    $sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
                    return;
                }
            }else{
                $sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
                return;
            }
        }
    }

}