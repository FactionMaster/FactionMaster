<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Route\HomeListPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeCommand extends BaseSubCommand {

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        $permissions = MainAPI::getMemberPermission($sender->getName());
        $UserEntity = MainAPI::getUser($sender->getName());
        if ((isset($permissions[Ids::PERMISSION_TP_FACTION_HOME]) && $permissions[Ids::PERMISSION_TP_FACTION_HOME]) || $UserEntity->rank == Ids::OWNER_ID) {
            Utils::processMenu(RouterFactory::get(HomeListPanel::SLUG), $sender->getPlayer());
        }else{
            $sender->sendMessage(" §c>> §4You don't have the permission to use that");
        }
    }

}