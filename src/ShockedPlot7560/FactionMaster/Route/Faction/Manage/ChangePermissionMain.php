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

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangePermissionMain implements Route {

    const SLUG = "changePermissionMain";

    public $PermissionNeed = [
        Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
    ];
    public $backMenu;

    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $this->buttons = [];
        if ($User->rank > Ids::RECRUIT_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "RECRUIT_RANK_NAME");
        if ($User->rank > Ids::MEMBER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "MEMBER_RANK_NAME");
        if ($User->rank > Ids::COOWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "COOWNER_RANK_NAME");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");
        $menu = $this->changePermissionMenu($message);
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "RECRUIT_RANK_NAME"):
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::RECRUIT_ID]);
                    break;
                case Utils::getText($this->UserEntity->name, "MEMBER_RANK_NAME"):
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::MEMBER_ID]);
                    break;
                case Utils::getText($this->UserEntity->name, "COOWNER_RANK_NAME"):
                    Utils::processMenu(RouterFactory::get(RankPermissionManage::SLUG), $Player, [Ids::COOWNER_ID]);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK");
                    Utils::processMenu($backMenu, $Player);
                    break;
            }
        };
    }

    private function changePermissionMenu(string $message = "") : SimpleForm {
        $menu =new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CHANGE_PERMISSION_TITLE"));
        if (count($this->buttons) == 1) $message .= Utils::getText($this->UserEntity->name, "NO_RANK");
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }
}