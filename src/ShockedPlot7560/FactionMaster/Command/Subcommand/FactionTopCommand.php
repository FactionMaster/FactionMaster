<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Route\TopFactionPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionTopCommand extends BaseSubCommand {

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        Utils::processMenu(RouterFactory::get(TopFactionPanel::SLUG), $sender->getPlayer());
    }

}