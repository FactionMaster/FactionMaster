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

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage;

use InvalidArgumentException;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\API\PermissionManager;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class RankPermissionManage implements Route {

    const SLUG = "rankPermissionManage";

    public $PermissionNeed = [
        Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
    ];
    public $backMenu;

    /** @var array */
    private $check;
    /** @var array */
    private $permissionsData;
    /** @var array */
    private $permissionsUser;
    /** @var array */
    private $permissionsFaction;
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ChangePermissionMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !\is_int($params[0])) throw new InvalidArgumentException("Please give the rank id in the first item of the \$params");
        $this->rank = $params[0];
        $this->permissionsData = PermissionManager::getAll();
        $this->permissionsUser = $UserPermissions;
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->permissionsFaction = $this->Faction->permissions[$this->rank];

        $this->check = [];
        foreach ($this->permissionsData as $key => $permission) {
            if ($User->rank == Ids::OWNER_ID || (isset($this->permissionsUser[$permission['id']]) && $this->permissionsUser[$permission['id']] === true)) {
                $this->check[] = $permission['nameCallable'];
            }else{
                unset($this->permissionsData[$key]);
            }
        }
        $menu = $this->createPermissionMenu();
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            $i =0;
            foreach ($this->permissionsData as $key => $permissionDa) {
                $this->Faction->permissions[$this->rank][$permissionDa['id']] = $data[$i];
                $i++;
            }
            if (MainAPI::updatePermissionFaction($this->Faction->name, $this->Faction->permissions)){
                Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_PERMISSION_UPDATE")]);
            }else{
                Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "ERROR")]);
            }
        };
    }

    private function createPermissionMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_PERMISSIONS_MAIN_TITLE"));
        foreach ($this->permissionsData as $value) {
            $menu->addToggle(call_user_func($value['nameCallable'], $this->UserEntity->name), $this->permissionsFaction[$value["id"]] ?? false);
        }
        return $menu;
    }
}