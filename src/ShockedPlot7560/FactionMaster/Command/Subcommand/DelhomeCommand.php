<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DelhomeCommand extends BaseSubCommand {

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
        if ((isset($permissions[Ids::PERMISSION_DELETE_FACTION_HOME]) && $permissions[Ids::PERMISSION_DELETE_FACTION_HOME]) || $UserEntity->rank == Ids::OWNER_ID) {
            if (MainAPI::getFactionHome($UserEntity->faction, $args["name"])) {
                if (MainAPI::removeHome($UserEntity->faction, $args['name'])) {
                    $sender->sendMessage(Utils::getText($sender->getName(), "SUCCESS_HOME_DELETE"));
                    return;
                }else{
                    $sender->sendMessage(Utils::getText($sender->getName(), "ERROR"));
                    return;
                }
            }else{
                $sender->sendMessage(Utils::getText($sender->getName(), "HOME_DONT_EXIST"));
                return;
            }
        }else{
            $sender->sendMessage(Utils::getText($sender->getName(), "DONT_PERMISSION"));
            return;
        }
    }

}