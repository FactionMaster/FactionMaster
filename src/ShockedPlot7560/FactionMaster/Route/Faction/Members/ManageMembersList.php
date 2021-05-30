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

namespace ShockedPlot7560\FactionMaster\Route\Faction\Members;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMembersList implements Route {

    const SLUG = "manageMembersList";

    public $PermissionNeed = [Ids::PERMISSION_CHANGE_MEMBER_RANK, Ids::PERMISSION_KICK_MEMBER];
    public $backMenu;

    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageMainMembers::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->UserEntity = $User;

        $this->buttons = [];
        foreach ($Faction->members as $key => $Member) {
            if ($key === $player->getName()) continue;
            if ($Member < $this->UserEntity->rank) {
                $this->buttons[] = $key;
            }
        }
        $this->buttons[] = "§4Back";

        if (isset($params[0])) $message = $params[0];
        if ((count($Faction->members) - 1) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_MEMBERS");
        
        $menu = $this->manageMembersListMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu($backMenu, $player);
                return;
            }
            if (isset($this->buttons[$data])) {
                Utils::processMenu(RouterFactory::get(ManageMember::SLUG), $player, [MainAPI::getUser($this->buttons[$data])]);
            }
            return;
        };
    }

    private function manageMembersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBERS_LIST_PANEL_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}