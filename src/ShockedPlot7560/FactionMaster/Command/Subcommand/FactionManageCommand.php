<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Route\Faction\Manage\ManageFactionMain;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionManageCommand extends BaseSubCommand {

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        $UserEntity = MainAPI::getUser($sender->getName());
        if ($UserEntity->faction !== null) {
            Utils::processMenu(RouterFactory::get(ManageFactionMain::SLUG), $sender->getPlayer());
        }else{
            $sender->sendMessage(Utils::getText($sender->getName(), "NEED_FACTION"));
        }
    }

}