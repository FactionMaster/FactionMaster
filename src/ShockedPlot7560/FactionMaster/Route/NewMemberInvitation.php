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

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use pocketmine\Server;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\AcceptMemberRequest;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationSendEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\ManageMainMembers;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewMemberInvitation implements Route {

    const SLUG = "memberInvitationCreate";

    public $PermissionNeed = [
        PermissionIds::PERMISSION_SEND_MEMBER_INVITATION
    ];
    public $backMenu;
    /** @var UserEntity */
    private $UserEntity;
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageMainMembers::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());

        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createInvitationMenu($message);
        $player->sendForm($menu);
    }

    public function call() : callable{
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($backMenu) {
            if ($data === null) return;
            
            if ($data[1] !== "") {
                $targetName = Server::getInstance()->getPlayer($data[1]);
                $targetName = $targetName === null ? $data[1] : $targetName->getName();
                $UserRequest = MainAPI::getUser($targetName);
                $FactionPlayer = MainAPI::getFactionOfPlayer($Player->getName());
                if (count($FactionPlayer->members) >= $FactionPlayer->max_player) {
                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "MAX_PLAYER_REACH"));
                    $Player->sendForm($menu);;
                    return;
                }
                if ($UserRequest instanceof UserEntity) {
                    if (!MainAPI::getFactionOfPlayer($targetName) instanceof FactionEntity) {
                        $Faction = $this->Faction;
                        if (!MainAPI::areInInvitation($Faction->name, $targetName, InvitationSendEvent::MEMBER_TYPE)) {
                            if (MainAPI::areInInvitation($targetName, $Faction->name, InvitationSendEvent::MEMBER_TYPE)) {
                                MainAPI::addMember($Faction->name, $UserRequest->name);
                                Utils::newMenuSendTask(new MenuSendTask(
                                    function () use ($UserRequest, $Faction) {
                                        return MainAPI::getUser($UserRequest->name)->faction === $Faction->name;
                                    },
                                    function () use ($UserRequest, $Player, $Faction, $backMenu) {
                                        (new FactionJoinEvent($UserRequest, $Faction))->call();
                                        $Request = MainAPI::$invitation[$UserRequest->name . "|" . $Faction->name];
                                        MainAPI::removeInvitation($UserRequest->name, $Faction->name, "member");
                                        Utils::newMenuSendTask(new MenuSendTask(
                                            function () use ($UserRequest, $Faction) {
                                                return !MainAPI::areInInvitation($UserRequest->name, $Faction->name, "member");
                                            },
                                            function () use ($Request, $Player, $backMenu, $UserRequest) {
                                                (new InvitationAcceptEvent($Player, $Request))->call();
                                                Utils::processMenu($backMenu, $Player, [Utils::getText($Player->getName(), "SUCCESS_ACCEPT_REQUEST", ['name' => $UserRequest->name])] );
                                            },
                                            function () use ($Player) {
                                                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                            }
                                        ));
                                    },
                                    function () use ($Player) {
                                        Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                    }
                                ));
                            }else{
                                MainAPI::makeInvitation($Faction->name, $targetName, InvitationSendEvent::MEMBER_TYPE);
                                Utils::newMenuSendTask(new MenuSendTask(
                                    function () use ($Faction, $targetName) {
                                        return MainAPI::areInInvitation($Faction->name, $targetName, InvitationSendEvent::MEMBER_TYPE);
                                    },
                                    function () use ($Player, $targetName, $backMenu, $data, $Faction) {
                                        (new InvitationSendEvent($Player, $Faction->name, $targetName, InvitationSendEvent::MEMBER_TYPE))->call();
                                        Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_SEND_INVITATION", ['name' => $data[1]])] );
                                    },
                                    function () use ($Player) {
                                        Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                    }
                                ));
                            }
                        }else{
                            $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_PENDING_INVITATION"));
                            $Player->sendForm($menu);;
                        }
                    }else{
                        $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "PLAYER_HAVE_ALREADY_FACTION"));
                        $Player->sendForm($menu);;
                    }
                }else{
                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "USER_DONT_EXIST"));
                    $Player->sendForm($menu);;
                } 
            }else{
                Utils::processMenu($backMenu, $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_TITLE"));
        $menu->addLabel(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_CONTENT") . "\n" . $message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_INPUT_CONTENT"));
        return $menu;
    }
}