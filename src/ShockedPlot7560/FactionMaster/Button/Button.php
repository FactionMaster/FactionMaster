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

namespace ShockedPlot7560\FactionMaster\Button;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

/**
 * @param string $slug
 * @param callable $content The function use to get the content, get in parameter the name of player
 * @param callable $callable The function call when the button are click, get in parameter the player instance
 * @param (int|array)[] $permissions The array of the button permissions, only one of it are necessary to show the button
 */
class Button {

    private $slug;
    private $content;
    private $permissions;
    private $callable;
    private $imgPath;
    private $imgType;

    public function __construct(string $slug, callable $content, callable $callable, array $permissions = [], string $imgPath = "", int $imgType = SimpleForm::IMAGE_TYPE_URL)
    {
        $this->slug = $slug;
        $this->content = $content;
        $this->permissions = $permissions;
        $this->callable = $callable;
        $this->imgPath = $imgPath;
        $this->imgType = $imgType;
    }

    public function getSlug() : string {
        return $this->slug;
    }

    public function getContent(string $playerName) : string {
        return \call_user_func($this->content, $playerName);
    }

    public function getPermissions() : array {
        return $this->permissions;
    }

    public function hasAccess(string $playerName) : bool {
        if (count($this->permissions) == 0) return true;
        $User = MainAPI::getUser($playerName);
        if ($User->rank == Ids::OWNER_ID) return true;
        $PermissionsPlayer = MainAPI::getMemberPermission($playerName);
        foreach ($this->getPermissions() as $Permission) {
            if (!is_array($Permission) && $PermissionsPlayer !== null) {
                if (isset($PermissionsPlayer[$Permission]) && $PermissionsPlayer[$Permission]) return true;
            }elseif (is_array($Permission) && $Permission[0] === Utils::POCKETMINE_PERMISSIONS_CONSTANT) {
                return Main::getInstance()->getServer()->getPlayerExact($playerName)->hasPermission($Permission[1]);
            }
        }
        return false;
    }

    public function getCallable() : callable {
        return $this->callable;
    }

    public function call(Player $Player) {
        return call_user_func($this->getCallable(), $Player);
    }

    public function getImgPath(): string {
        return $this->imgPath;
    }

    public function getImgType(): int {
        return $this->imgType;
    }
}