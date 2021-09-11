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

use ShockedPlot7560\FactionMaster\libs\CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;

class MapCommand extends BaseSubCommand {

    const SYMBOL = [
        "/", "\\", "#", "$", "?", "%", "=", "&", "^", "$",
    ];

    const DIRECTION = [
        "N" => 'N',
        "NE" => '/',
        "E" => 'E',
        "SE" => '\\',
        "S" => 'S',
        "SW" => '/',
        "W" => 'W',
        "NW" => '\\',
        "NONE" => '+',
    ];

    const PLAYER_SYMBOL = "+";
    const PLAYER_COLOR = "§b";

    const WILDERNESS_SYMBOL = "-";
    const WILDERNESS_COLOR = "§7";

    const NO_SYMBOL = "0";
    const CLAIM_COLOR = "§f";
    const CLAIM_ALLIES_COLOR = "§e";
    const CLAIM_ENNEMIE_COLOR = "§4";
    const CLAIM_OWN_COLOR = "§b";

    const HEIGHT = 10;
    const WIDTH = 48;

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void {
        if (!$sender instanceof Player) {
            return;
        }

        $Player = $sender->getPlayer();
        $UserEntity = MainAPI::getUser($Player->getName());
        if (!$UserEntity instanceof UserEntity) {
            return;
        }

        $CentralChunk = $Player->getLevel()->getChunkAtPosition($Player);
        $SymbolData = self::SYMBOL;
        $SymbolCursor = 0;
        $ClaimData = [];

        $X = round($Player->getX());
        $Z = round($Player->getZ());
        $ChunkFaction = MainAPI::getFactionClaim($Player->getLevel()->getName(), $CentralChunk->getX(), $CentralChunk->getZ());
        if ($ChunkFaction instanceof ClaimEntity && $ChunkFaction->faction === $UserEntity->faction) {
            $FactionLabelColor = self::CLAIM_OWN_COLOR;
        } elseif ($ChunkFaction instanceof ClaimEntity && MainAPI::isAlly($UserEntity->faction, $ChunkFaction->faction)) {
            $FactionLabelColor = self::CLAIM_ALLIES_COLOR;
        } else {
            $FactionLabelColor = self::CLAIM_ENNEMIE_COLOR;
        }
        $FactionLabel = $ChunkFaction instanceof ClaimEntity ? $FactionLabelColor . $ChunkFaction->faction : "§5" . "Wilderness";
        $middleString = ".[ §2($X,$Z) " . $FactionLabel . " §6].";
        $lenMiddle = \strlen($middleString) - 3;
        $bottom = "";
        for ($i = 0; $i < \floor((48 - $lenMiddle) / 2); $i++) {
            $bottom .= "_";
        }
        $Player->sendMessage("§6$bottom" . "$middleString" . "$bottom");
        for ($mZ = 0; $mZ < self::HEIGHT; $mZ++) {
            $lign = "";
            for ($mX = 0; $mX < self::WIDTH; $mX++) {
                $X = $CentralChunk->getX() + $mX - (self::WIDTH / 2);
                $Z = $CentralChunk->getZ() + $mZ - (self::HEIGHT / 2);

                if ($CentralChunk->getX() == $X && $CentralChunk->getZ() == $Z) {
                    $lign .= self::PLAYER_COLOR . self::PLAYER_SYMBOL;
                    continue;
                } else {
                    $Faction = MainAPI::getFactionClaim($Player->getLevel()->getName(), $X, $Z);
                    if ($Faction instanceof ClaimEntity) {
                        if (!isset($ClaimData[$Faction->faction])) {
                            if (!isset($SymbolData[$SymbolCursor])) {
                                $lign .= self::CLAIM_COLOR . self::NO_SYMBOL;
                                continue;
                            } else {
                                if ($UserEntity->faction !== null) {
                                    if (MainAPI::isAlly($UserEntity->faction, $Faction->faction)) {
                                        $color = self::CLAIM_ALLIES_COLOR;
                                    } elseif ($Faction->faction === $UserEntity->faction) {
                                        $color = self::CLAIM_OWN_COLOR;
                                    } else {
                                        $color = self::CLAIM_COLOR;
                                    }
                                } else {
                                    $color = self::CLAIM_COLOR;
                                }
                                $ClaimData[$Faction->faction] = [
                                    "SYMBOL" => $SymbolData[$SymbolCursor],
                                    "COLOR" => $color,
                                    "FACTION" => $Faction,
                                ];
                                $SymbolCursor++;
                            }
                        }
                        $Data = $ClaimData[$Faction->faction];
                        $lign .= $Data['COLOR'] . $Data['SYMBOL'];
                    } else {
                        $lign .= self::WILDERNESS_COLOR . self::WILDERNESS_SYMBOL;
                    }
                }
            }
            if ($mZ <= 2) {
                $degrees = ($Player->getYaw() - 157) % 360;
                if ($degrees < 0) {
                    $degrees += 360;
                }

                $Direction = array_keys(self::DIRECTION)[intval(floor($degrees / 45))];
                $Compass = [["NW", "N", "NE"], ["W", "NONE", "E"], ["SW", "S", "SE"]];
                $Compass = \array_map(function (array $DirectionsData) use ($Direction) {
                    $text = "";
                    foreach ($DirectionsData as $DirectionData) {
                        if ($Direction === $DirectionData) {
                            $text .= "§c";
                        } else {
                            $text .= "§e";
                        }
                        $text .= self::DIRECTION[$DirectionData];
                    }
                    return $text;
                }, $Compass);
                $lign = $Compass[$mZ] . \substr($lign, 12, \strlen($lign));
            }
            $Player->sendMessage($lign);
        }
        $text = "";
        foreach ($ClaimData as $key => $Claim) {
            $newString = " " . $Claim['COLOR'] . $Claim['SYMBOL'] . ": " . $Claim['FACTION']->faction;
            if (\strlen($text . $newString) > 48) {
                $Player->sendMessage($text . $newString);
                $text = "";
            } else {
                $text .= $newString;
            }
        }
        $Player->sendMessage($text);
    }

}