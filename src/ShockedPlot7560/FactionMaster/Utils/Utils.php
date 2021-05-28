<?php

namespace ShockedPlot7560\FactionMaster\Utils;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\plugin\PluginLogger;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;

class Utils {

    public static function  generateButton(SimpleForm $Form, array $buttons) {
        foreach ($buttons as $button) {
            $Form->addButton($button);
        }
        return $Form;
    }

    /**
     * Used to process the Route given for the player
     */
    public static function processMenu(Route $route, Player $Player, ?array $params = null) {
        $UserEntity = MainAPI::getUser($Player->getName());
        $UserPermissions = MainAPI::getMemberPermission($Player->getName());
        if ($UserPermissions === null) {
            $UserPermissions = [];
        }
        if ($UserEntity->rank !== Ids::OWNER_ID && isset($route->PermissionNeed)) {
            foreach ($route->PermissionNeed as $Permission) {
                if (isset($UserPermissions[$Permission]) && $UserPermissions[$Permission]) {
                    $route($Player, $UserEntity, $UserPermissions, $params);
                    return;
                }
            }
        }
        $route($Player, $UserEntity, $UserPermissions, $params);
    }

    public static function replaceParams(string $string, array $data): string {
        foreach ($data as $key => $value) {
            $string = \str_replace("{{".$key."}}", $value, $string);
        }
        return $string;
    }

    public static function getPermissionData() : array {
        return [
            [
                "text" => "Promote/Demote members",
                "id" => Ids::PERMISSION_CHANGE_MEMBER_RANK
            ],[
                "text" => "Kick members",
                "id" => Ids::PERMISSION_KICK_MEMBER
            ],[
                "text" => "Accept member demand",
                "id" => Ids::PERMISSION_ACCEPT_MEMBER_DEMAND
            ],[
                "text" => "Refuse member demand",
                "id" => Ids::PERMISSION_REFUSE_MEMBER_DEMAND
            ],[
                "text" => "Send member's invitation",
                "id" => Ids::PERMISSION_SEND_MEMBER_INVITATION
            ],[
                "text" => "Delete pending member's invitation",
                "id" => Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION
            ],[
                "text" => "Accept alliance demand",
                "id" => Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND
            ],[
                "text" => "Refuse alliance demand",
                "id" => Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND
            ],[
                "text" => "Send alliance's invitation",
                "id" => Ids::PERMISSION_SEND_ALLIANCE_INVITATION
            ],[
                "text" => "Delete pending alliance's invitation",
                "id" => Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION
            ],[
                "text" => "Manage lower rank permissions",
                "id" => Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
            ],[
                "text" => "Change the faction message",
                "id" => Ids::PERMISSION_CHANGE_FACTION_MESSAGE
            ],[
                "text" => "Change the faction description",
                "id" => Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION
            ],[
                "text" => "Change the faction visibility",
                "id" => Ids::PERMISSION_CHANGE_FACTION_VISIBILITY
            ],[
                "text" => "Use the command /f claim, to claim a chunk",
                "id" => Ids::PERMISSION_ADD_CLAIM
            ],[
                "text" => "Use the command /f unclaim, to unclaim a chunk",
                "id" => Ids::PERMISSION_REMOVE_CLAIM
            ],[
                "text" => "Use the command /f home, and can tp to the home",
                "id" => Ids::PERMISSION_TP_FACTION_HOME
            ],[
                "text" => "Use the command /f sethome, to set a home",
                "id" => Ids::PERMISSION_ADD_FACTION_HOME
            ],[
                "text" => "Use the command /f delhome, to delete a home",
                "id" => Ids::PERMISSION_DELETE_FACTION_HOME
            ]
        ];
    }

    public static function printLogo(PluginLogger $logger) {
        $text = "\n      
         ______           __  _                __  ___           __\n           
        / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____\n
       / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/\n
      / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  \n  
     /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/  \n   
                                                                              ";
        $logger->info($text);
    }

    public static function claimToString($X, $Z, $World) {
        return $X . "|" . $Z . "|" . $World;
    }

    public static function homeToString($X, $Y, $Z, $World) {
        return $X . "|" . $Y . "|" . $Z . "|" . $World;
    }

    public static function homeToArray($X, $Y, $Z, $World) {
        return [
            "x" => $X,
            "y" => $Y,
            "z" => $Z,
            "world" => $World
          ];
    }

    /**
     * @param string $key
     */
    public static function getConfig(string $key) {
        $Config = new Config(Main::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        return $Config->get($key);
    }
    
    /**
     * @param string $key
     */
    public static function getConfigLang(string $key) {
        $Config = new Config(Main::getInstance()->getDataFolder() . "translation.yml", Config::YAML);
        return $Config->get($key);
    }

    public static function getText(string $playerName, string $slug, array $args = []) : string {
        $Playerlang = MainAPI::$languages[$playerName] ?? self::getConfigLang("default-language");
        $FileName = self::getConfigLang("languages")[$Playerlang];
        $Config = new Config(Main::getInstance()->getDataFolder() . "Translation/$FileName.yml", Config::YAML);
        $Text = self::replaceParams($Config->get($slug), $args);
        return $Text;
    }
}