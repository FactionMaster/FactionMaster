<?php

namespace ShockedPlot7560\FactionMaster\Command;

use CortexPE\Commando\BaseCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Command\Subcommand\ClaimCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\DelhomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionCreateCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionManageCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\FactionTopCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\HomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\HomeTpCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\SethomeCommand;
use ShockedPlot7560\FactionMaster\Command\Subcommand\UnclaimCommand;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class FactionCommand extends BaseCommand{

    protected function prepare(): void {
        $this->registerSubCommand(new FactionCreateCommand("create", "Open the faction creation menu"));
        $this->registerSubCommand(new FactionTopCommand("top", "Open the faction leaderboards menu"));
        $this->registerSubCommand(new FactionManageCommand("manage", "Open the faction control menu"));
        $this->registerSubCommand(new ClaimCommand("claim", "Claim the chunk"));
        $this->registerSubCommand(new UnclaimCommand("unclaim", "Unclaim the chunk"));
        $this->registerSubCommand(new SethomeCommand("sethome", "Add a home to your location"));
        $this->registerSubCommand(new DelhomeCommand("delhome", "Remove the given home"));
        $this->registerSubCommand(new HomeTpCommand("tp", "Tp you to the given home"));
        $this->registerSubCommand(new HomeCommand("home", "Open the faction homes menu"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args) : void {
        if (!$sender instanceof Player) {
            return;
        }
        if(count($args) == 0) {
            Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $sender->getPlayer());
            return;
        }
    }
}