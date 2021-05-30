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

namespace ShockedPlot7560\FactionMaster\Route;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Invitations\DemandList;
use ShockedPlot7560\FactionMaster\Route\Invitations\InvitationList;
use ShockedPlot7560\FactionMaster\Route\Invitations\NewInvitation;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageInvitationMain implements Route {

    const SLUG = "manageInvitationMain";

    public $PermissionNeed = [];
    public $backMenu;

    /** @var array */
    private $buttons;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->FormUI = Main::getInstance()->FormUI;
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {        
        $this->UserEntity = $User;
        $this->buttons = [];
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_SEND_INVITATION");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_INVITATION_PENDING");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_REQUEST_PENDING");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            switch ($data) {
                case count($this->buttons) - 1:
                    Utils::processMenu($backMenu, $player);
                    break;
                case count($this->buttons) - 2:
                    Utils::processMenu(RouterFactory::get(DemandList::SLUG), $player);
                    break;
                case count($this->buttons) - 3:
                    Utils::processMenu(RouterFactory::get(InvitationList::SLUG), $player);
                    break;
                case count($this->buttons) - 4:
                    Utils::processMenu(RouterFactory::get(NewInvitation::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "JOIN_FACTION_PANEL"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}