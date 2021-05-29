<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HelpCommand extends BaseSubCommand {

    protected function prepare(): void {
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        $sender->sendMessage("§8=§7=§8=§7=§8=§7=§8=§7=§8=§7= §bFactionMaster command §8=§7=§8=§7=§8=§7=§8=§7=§8=§7=");
        $sender->sendMessage(" §8>> §r§b/f: §7" . Utils::getText($sender->getName(), "COMMAND_FACTION_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f top: §7" . Utils::getText($sender->getName(), "COMMAND_TOP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f manage: §7" . Utils::getText($sender->getName(), "COMMAND_MANAGE_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f claim: §7" . Utils::getText($sender->getName(), "COMMAND_CLAIM_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f unclaim: §7" . Utils::getText($sender->getName(), "COMMAND_UNCLAIM_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f sethome <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_SETHOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f delhome <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_DELHOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f tp <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_TP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f home: §7" . Utils::getText($sender->getName(), "COMMAND_HOME_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f create: §7" . Utils::getText($sender->getName(), "COMMAND_CREATE_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f map: §7" . Utils::getText($sender->getName(), "COMMAND_MAP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f help: §7" . Utils::getText($sender->getName(), "COMMAND_HELP_DESCRIPTION"));
        $sender->sendMessage(" §8>> §r§b/f info <:name>: §7" . Utils::getText($sender->getName(), "COMMAND_HELP_DESCRIPTION"));
    }

}