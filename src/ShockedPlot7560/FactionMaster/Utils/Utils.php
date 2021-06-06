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

namespace ShockedPlot7560\FactionMaster\Utils;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use pocketmine\plugin\PluginLogger;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MenuOpenEvent;
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
        if ($UserEntity instanceof UserEntity && $UserEntity->rank !== Ids::OWNER_ID && isset($route->PermissionNeed)) {
            foreach ($route->PermissionNeed as $Permission) {
                if (isset($UserPermissions[$Permission]) && $UserPermissions[$Permission]) {
                    $ev = new MenuOpenEvent($Player, $route);
                    $ev->call();
                    if ($ev->isCancelled()) return;
                    $route($Player, $UserEntity, $UserPermissions, $params);
                    return;
                }
            }
        }
        $ev = new MenuOpenEvent($Player, $route);
        $ev->call();
        if ($ev->isCancelled()) return;
        $route($Player, $UserEntity, $UserPermissions, $params);
    }

    public static function replaceParams(string $string, array $data): string {
        foreach ($data as $key => $value) {
            $string = \str_replace("{{".$key."}}", $value, $string);
        }
        return $string;
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
        $Playerlang = MainAPI::getPlayerLang($playerName);
        $FileName = self::getConfigLang("languages")[$Playerlang];
        $Config = new Config(Main::getInstance()->getDataFolder() . "Translation/$FileName.yml", Config::YAML);
        $Text = self::replaceParams($Config->get($slug), $args);
        return $Text;
    }

    public static function getXpLevel(int $level) : int {
        return 1000*pow(1.09, $level);
    }
}