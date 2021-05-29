<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use DateTime;
use pocketmine\command\CommandSender;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class InfoCommand extends BaseSubCommand {

    protected function prepare(): void {
        $this->registerArgument(0, new RawStringArgument("name"));
    }

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!isset($args['name'])) {
            $this->sendUsage();
            return;
        }
        $Faction = MainAPI::getFaction($args['name']);
        if ($Faction === null) {
            $sender->sendMessage(Utils::getText($sender->getName(), "FACTION_DONT_EXIST"));
            return;
        }
        $middleString = ".[ §a" . $Faction->name . " §6].";
        $lenMiddle = \strlen($middleString) - 4;
        $bottom = "";
        for ($i=0; $i < \floor((48 - $lenMiddle) / 2); $i++) { 
            $bottom .= "_";
        }
        $sender->sendMessage("§6" . $bottom . $middleString . $bottom );
        $description = ($Faction->description === "" ? Utils::getText($sender->getName(), "COMMAND_NO_DESCRIPTION") : $Faction->description);
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_DESCRIPTION", ['description' => $description]));
        switch ($Faction->visibility) {
            case Ids::PUBLIC_VISIBILITY:
                $visibility = "§a" . Utils::getText($sender->getName(), "PUBLIC_VISIBILITY_NAME");
                break;
            case Ids::PRIVATE_VISIBILITY:
                $visibility = "§4" . Utils::getText($sender->getName(), "PRIVATE_VISIBILITY_NAME");
                break;
            case Ids::INVITATION_VISIBILITY:
                $visibility = "§6" . Utils::getText($sender->getName(), "INVITATION_VISIBILITY_NAME");
                break;
        }
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_VISIBILITY", ['ally' => $visibility]));
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_LEVEL", ['level' => $Faction->level, 'power' => $Faction->power]));
        $Ally = "";
        foreach ($Faction->ally as $key => $ally) {
            if ($key == \count($Faction->ally) - 1) {
                $Ally .= $ally;
            }else{
                $Ally .= $ally . ", ";
            }
        }
        if (\count($Faction->ally) == 0) {
            $Ally = Utils::getText($sender->getName(), "COMMAND_NO_ALLY");
        }
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_ALLY", ['ally' => $Ally]));
        $Members = "";
        $i = 0;
        foreach ($Faction->members as $member => $rank) {
            switch ($rank) {
                case Ids::OWNER_ID:
                    $Members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_OWNER", ['name' => $member]);
                    break;
                case Ids::COOWNER_ID:
                    $Members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_COOWNER", ['name' => $member]);
                    break;
                case Ids::MEMBER_ID:
                    $Members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_MEMBER", ['name' => $member]);
                    break;
                case Ids::RECRUIT_ID:
                    $Members .= Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER_RECRUIT", ['name' => $member]);
                    break;
            }
            if ($i != \count($Faction->members) - 1) {
                $Members .= " / ";
            }
            $i++;
        }
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_MEMBER", ['members' => $Members]));
        $Date = new DateTime($Faction->date);
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_DATE", ['date' => $Date->format("d M")]));
    }

}