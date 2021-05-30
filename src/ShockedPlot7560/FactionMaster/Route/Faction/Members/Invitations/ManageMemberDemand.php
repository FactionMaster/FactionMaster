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

namespace ShockedPlot7560\FactionMaster\Route\Faction\Members\Invitations;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMemberDemand implements Route {
    
    const SLUG = "manageMemberDemand";

    public $PermissionNeed = [
        Ids::PERMISSION_ACCEPT_MEMBER_DEMAND,
        Ids::PERMISSION_REFUSE_MEMBER_DEMAND
    ];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var InvitationEntity */
    private $invitation;


    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(MemberDemandList::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the invitation instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_ACCEPT_MEMBER_DEMAND]) && $UserPermissions[Ids::PERMISSION_ACCEPT_MEMBER_DEMAND]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_ACCEPT_REQUEST");
        if ((isset($UserPermissions[Ids::PERMISSION_REFUSE_MEMBER_DEMAND]) && $UserPermissions[Ids::PERMISSION_REFUSE_MEMBER_DEMAND]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_REFUSE_REQUEST");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageInvitationMenu();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($invitation, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK"):
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_REFUSE_REQUEST"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->receiver, $invitation->sender),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_DELETE_REQUEST"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_DELETE_REQUEST")
                    ]);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_ACCEPT_REQUEST"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callAccept($invitation->receiver, $invitation->sender),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_ACCEPT_REQUEST"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_ACCEPT_REQUEST")
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageInvitationMenu() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        if (count($this->buttons) == 1) {
            $menu->setContent(Utils::getText($this->UserEntity->name, "NO_ACTION_POSSIBLE"));
        }
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_MEMBER_REQUEST_TITLE", ['name' => $this->invitation->sender]));
        return $menu;
    }

    private function callDelete(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_DELETE_REQUEST", ['name' => $playerName]);
                if (!MainAPI::removeInvitation($playerName, $factionName, "member")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

    private function callAccept(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation) {
            if ($data === null) return;
            if ($data) {
                $Faction = MainAPI::getFaction($factionName);
                if (count($Faction->members) < $Faction->max_player) {
                    $message = Utils::getText($this->UserEntity->name, "SUCCESS_ACCEPT_REQUEST", ['name' => $playerName]);
                    if (!MainAPI::addMember($factionName, $playerName)) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                    if (!MainAPI::removeInvitation($playerName, $factionName, "member")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                    Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player, [$message]);
                }else{
                    $message = Utils::getText($this->UserEntity->name, "MAX_PLAYER_REACH");
                    Utils::processMenu(RouterFactory::get(ManageMainMembers::SLUG), $Player, [$message]);
                }
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}