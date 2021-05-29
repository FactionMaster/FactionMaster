<?php

namespace ShockedPlot7560\FactionMaster\Command\Subcommand;

use CortexPE\Commando\BaseSubCommand;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;

class MapCommand extends BaseSubCommand {

    const SYMBOL = [
        "/","\\","#","$","?","%","=","&","^","$"
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
        "NONE" => '+'
    ];

    const PLAYER_SYMBOL = "+";
    const PLAYER_COLOR = "§b";

    const WILDERNESS_SYMBOL = "-";
    const WILDERNESS_COLOR = "§7";

    const NO_SYMBOL = "0";
    const CLAIM_COLOR = "§f";
    const CLAIM_ALLIES_COLOR = "§a";

    const HEIGHT = 10;
    const WIDTH = 48;

    protected function prepare(): void {}

    public function onRun(CommandSender $sender, string $aliasUsed, array $args): void
    {
        if (!$sender instanceof Player) return;
        $Player = $sender->getPlayer();
        $UserEntity = MainAPI::getUser($Player->getName());
        $CentralChunk = $Player->getLevel()->getChunkAtPosition($Player);
        $map = [];
        $SymbolData = self::SYMBOL;
        $SymbolCursor = 0;
        $ClaimData = [];

        $X = round($Player->getX());
        $Z = round($Player->getZ());
        $ChunkFaction = MainAPI::getFactionClaim($Player->getLevel()->getName(), $CentralChunk->getX(), $CentralChunk->getZ());
        $FactionLabel = $ChunkFaction !== null ? ("§4" .$ChunkFaction) : ("§a" . "Wilderness");
        $middleString = ".[ §2($X,$Z) " . $FactionLabel . " §6].";
        $lenMiddle = \strlen($middleString) - 3;
        $bottom = "";
        for ($i=0; $i < \floor((48 - $lenMiddle) / 2); $i++) { 
            $bottom .= "_";
        }
        $Player->sendMessage("§6$bottom" . "$middleString" . "$bottom");
        for ($mZ=0; $mZ < self::HEIGHT; $mZ++) { 
            $lign = "";
            for ($mX=0; $mX < self::WIDTH; $mX++) { 
                $X = $CentralChunk->getX() + $mX - (self::WIDTH / 2);
                $Z = $CentralChunk->getZ() + $mZ - (self::HEIGHT / 2);

                if ($CentralChunk->getX() == $X && $CentralChunk->getZ() == $Z) {
                    $lign .= self::PLAYER_COLOR . self::PLAYER_SYMBOL;
                    continue;
                }else{
                    $Faction = MainAPI::getFactionClaim($Player->getLevel()->getName(), $X, $Z);
                    if ($Faction !== null) {
                        if (!isset($ClaimData[$Faction])) {
                            if (!isset($SymbolData[$SymbolCursor])) {
                                $lign .= self::CLAIM_COLOR . self::NO_SYMBOL;
                                continue;
                            }else{
                                if ($UserEntity->faction !== null) {
                                    if (MainAPI::isAlly($UserEntity->faction, $Faction)) {
                                        $color = self::CLAIM_ALLIES_COLOR;
                                    }else{
                                        $color = self::CLAIM_COLOR;
                                    }
                                }
                                $ClaimData[$Faction] = [
                                    "SYMBOL" => $SymbolData[$SymbolCursor],
                                    "COLOR" => $color,
                                    "FACTION" => $Faction
                                ];
                                $SymbolCursor++;
                            }
                        }
                        $Data = $ClaimData[$Faction];
                        $lign .= $Data['COLOR'] . $Data['SYMBOL'];
                    }else{
                        $lign .= self::WILDERNESS_COLOR . self::WILDERNESS_SYMBOL;
                    }
                }
            }
            if ($mZ <= 2) {
                $degrees = ($Player->getYaw() - 157) % 360;
                if ($degrees < 0) $degrees += 360;

                $Direction = array_keys(self::DIRECTION)[intval(floor($degrees / 45))];
                $Compass = [["NW", "N", "NE"], ["W", "NONE", "E"], ["SW", "S", "SE"]];
                $Compass = \array_map(function (array $DirectionsData) use ($Direction) {
                    $text = "";
                    foreach ($DirectionsData as $DirectionData) {
                        if ($Direction === $DirectionData) {
                            $text .= "§c";
                        }else{
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
            $newString = " " . $Claim['COLOR'] . $Claim['SYMBOL'] . ": " . $Claim['FACTION'];
            if (\strlen($text . $newString) > 48) {
                $Player->sendMessage($text . $newString);
                $text = "";
            }else{
                $text .= $newString;
            }
        }
        $Player->sendMessage($text);
    }

}