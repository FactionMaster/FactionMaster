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
        $this->registerSubCommand(new FactionCreateCommand("create", Utils::getText("", "COMMAND_CREATE_DESCRIPTION")));
        $this->registerSubCommand(new FactionTopCommand("top", Utils::getText("", "COMMAND_TOP_DESCRIPTION")));
        $this->registerSubCommand(new FactionManageCommand("manage", Utils::getText("", "COMMAND_MANAGE_DESCRIPTION")));
        $this->registerSubCommand(new ClaimCommand("claim", Utils::getText("", "COMMAND_CLAIM_DESCRIPTION")));
        $this->registerSubCommand(new UnclaimCommand("unclaim", Utils::getText("", "COMMAND_UNCLAIM_DESCRIPTION")));
        $this->registerSubCommand(new SethomeCommand("sethome", Utils::getText("", "COMMAND_SETHOME_DESCRIPTION")));
        $this->registerSubCommand(new DelhomeCommand("delhome", Utils::getText("", "COMMAND_DELHOME_DESCRIPTION")));
        $this->registerSubCommand(new HomeTpCommand("tp", Utils::getText("", "COMMAND_TP_DESCRIPTION")));
        $this->registerSubCommand(new HomeCommand("home", Utils::getText("", "COMMAND_HOME_DESCRIPTION")));
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