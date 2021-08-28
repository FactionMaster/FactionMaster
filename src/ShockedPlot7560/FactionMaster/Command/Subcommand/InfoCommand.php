<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/ 
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560 
 * @link https://github.com/ShockedPlot7560
 * 
 *
*/

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\args\RawStringArgument;
use CortexPE\Commando\BaseSubCommand;
use DateTime;
use pocketmine\command\CommandSender;
use ShockedPlot7560\FactionMaster\API\MainAPI;
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
            default:
                $visibility = "Unknow";
                break;
        }
        $sender->sendMessage(Utils::getText($sender->getName(), "COMMAND_INFO_VISIBILITY", ['visibility' => $visibility]));
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