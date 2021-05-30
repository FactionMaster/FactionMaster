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
use jojoe77777\FormAPI\FormAPI;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\Invitations\MemberDemandList;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\Invitations\MemberInvitationList;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\Invitations\NewMemberInvitation;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMainMembers implements Route {

    const SLUG = "manageMainMembers";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_MEMBER_INVITATION, 
        Ids::PERMISSION_KICK_MEMBER, 
        Ids::PERMISSION_CHANGE_MEMBER_RANK,
        Ids::PERMISSION_ACCEPT_MEMBER_DEMAND,
        Ids::PERMISSION_REFUSE_MEMBER_DEMAND,
        Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION
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
        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_SEND_MEMBER_INVITATION]) && $UserPermissions[Ids::PERMISSION_SEND_MEMBER_INVITATION]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_SEND_INVITATION");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_INVITATION_PENDING");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_REQUEST_PENDING");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_MANAGE_MEMBERS");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $message = "";
        if (isset($params[0])) $message = $params[0];
        $menu = $this->manageMainMembersMenu($message);
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
                    Utils::processMenu(RouterFactory::get(ManageMembersList::SLUG), $player);
                    break;
                case count($this->buttons) - 3:
                    Utils::processMenu(RouterFactory::get(MemberDemandList::SLUG), $player);
                    break;
                case count($this->buttons) - 4:
                    Utils::processMenu(RouterFactory::get(MemberInvitationList::SLUG), $player);
                    break;
                case count($this->buttons) - 5:
                    Utils::processMenu(RouterFactory::get(NewMemberInvitation::SLUG), $player);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageMainMembersMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBERS_MAIN_PANEL_TITLE"));
        if ($message !== "") $menu->setContent($message);
        return $menu;
    }

}