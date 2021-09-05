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
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\MenuOpenEvent;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;

class Utils {

    const POCKETMINE_PERMISSIONS_CONSTANT = "Pocketmine";

    public static function generateButton(SimpleForm $Form, array $buttons): SimpleForm {
        foreach ($buttons as $button) {
            $Form->addButton($button);
        }
        return $Form;
    }

    /**
     * Used to process the Route given for the player
     */
    public static function processMenu(Route $route, Player $Player, ?array $params = null): void {
        $UserEntity = MainAPI::getUser($Player->getName());
        $UserPermissions = MainAPI::getMemberPermission($Player->getName());
        if ($UserPermissions === null) {
            $UserPermissions = [];
        }
        if ($UserEntity instanceof UserEntity && isset($route->PermissionNeed)) {
            $good = false;
            foreach ($route->PermissionNeed as $Permission) {
                if (is_string($Permission)) {
                    if (self::haveAccess($UserPermissions, $UserEntity, $Permission)) {
                        $good = true;
                    }
                } elseif (is_array($Permission) && $Permission[0] === self::POCKETMINE_PERMISSIONS_CONSTANT) {
                    if ($Player->hasPermission($Permission[1])) {
                        $good = true;
                    }
                }
            }
            if ($good === true) {
                $ev = new MenuOpenEvent($Player, $route);
                $ev->call();
                if ($ev->isCancelled()) {
                    return;
                }

                $route($Player, $UserEntity, $UserPermissions, $params);
                return;
            }
        }
        $ev = new MenuOpenEvent($Player, $route);
        $ev->call();
        if ($ev->isCancelled()) {
            return;
        }

        $route($Player, $UserEntity, $UserPermissions, $params);
    }

    public static function replaceParams(string $string, array $data): string {
        foreach ($data as $key => $value) {
            $string = \str_replace("{{" . $key . "}}", $value, $string);
        }
        return $string;
    }

    /**
     * @return string The formated string like X|Z|world
     */
    public static function claimToString($X, $Z, $World): string {
        return $X . "|" . $Z . "|" . $World;
    }

    /**
     * @return string The formated string like X|Y|Z|world
     */
    public static function homeToString($X, $Y, $Z, $World): string {
        return $X . "|" . $Y . "|" . $Z . "|" . $World;
    }

    public static function homeToArray($X, $Y, $Z, $World): array {
        return [
            "x" => $X,
            "y" => $Y,
            "z" => $Z,
            "world" => $World,
        ];
    }

    /**
     * @return bool|mixed
     */
    public static function getConfig(string $key) {
        $Config = new Config(Main::getInstance()->getDataFolder() . "config.yml", Config::YAML);
        return $Config->get($key);
    }

    /**
     * @return bool|mixed
     */
    public static function getConfigLang(string $key) {
        $Config = new Config(Main::getInstance()->getDataFolder() . "translation.yml", Config::YAML);
        return $Config->get($key);
    }

    public static function getText(string $playerName, string $slug, array $args = []): string {
        $Playerlang = MainAPI::getPlayerLang($playerName);
        $FileName = self::getConfigLang("languages")[$Playerlang] ?? self::getConfigLang("languages")["EN"];
        $Config = new Config(Main::getInstance()->getDataFolder() . "Translation/$FileName.yml", Config::YAML);
        $textNoReplace = $Config->get($slug);
        if ($textNoReplace === false) {
            $Config = new Config(Main::getInstance()->getDataFolder() . "Translation/en_EN.yml", Config::YAML);
            $textNoReplace = $Config->get($slug);
        }
        $Text = self::replaceParams($textNoReplace, $args);
        return $Text;
    }

    public static function getXpLevel(int $level): int {
        return 1000 * pow(1.09, $level);
    }

    public static function haveAccess(array $permission, UserEntity $UserEntity, int $id): bool {
        if ((int) $UserEntity->rank === Ids::OWNER_ID) {
            return true;
        }

        $PermissionManager = Main::getInstance()->getPermissionManager();
        if (!$PermissionManager->isRegister($id)) {
            return false;
        }

        return (isset($permission[$id]) && $permission[$id]);
    }

    public static function newMenuSendTask(MenuSendTask $task): TaskHandler {
        return Main::getInstance()->getScheduler()->scheduleRepeatingTask($task, 1);
    }

    public static function getDataFolder(): string {
        return Main::getInstance()->getDataFolder();
    }
}