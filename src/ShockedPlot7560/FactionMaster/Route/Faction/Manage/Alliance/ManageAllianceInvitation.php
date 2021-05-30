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

namespace ShockedPlot7560\FactionMaster\Route\Faction\Manage\Alliance;

use InvalidArgumentException;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAllianceInvitation implements Route {
    
    const SLUG = "manageAllianceInvitation";

    public $PermissionNeed = [
        Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION
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
        $this->backMenu = RouterFactory::get(AllianceMainMenu::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof InvitationEntity) throw new InvalidArgumentException("Need the target player instance");
        $this->invitation = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) && $UserPermissions[Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_DELETE_INVITATION");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageAlliance();
        $player->sendForm($menu);;
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
                case Utils::getText($this->UserEntity->name, "BUTTON_DELETE_INVITATION"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callDelete($invitation->sender, $invitation->receiver),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_DELETE_INVITATION"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_DELETE_INVITATION")
                    ]);
                    break;
                default:
                    return;
                    break;
            }
        };
    }

    private function manageAlliance() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        if (count($this->buttons) == 1) {
            $menu->setContent(Utils::getText($this->UserEntity->name, "NO_ACTION_POSSIBLE"));
        }
        $menu->setTitle(Utils::getText($this->UserEntity->name, "INVITATION_TITLE", ['name' => $this->invitation->receiver]));
        return $menu;
    }

    private function callDelete(string $factionName, string $playerName) : callable {
        $invitation = $this->invitation;
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($factionName, $playerName, $invitation, $backMenu) {
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_DELETE_INVITATION", ['name' => $playerName]);
                if (!MainAPI::removeInvitation($factionName, $playerName, "alliance")) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu($backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [$invitation]);
            }
        };
    }

}