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
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationAcceptEvent;
use ShockedPlot7560\FactionMaster\Event\InvitationSendEvent;
use ShockedPlot7560\FactionMaster\Route\ManageInvitationMain;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewInvitation implements Route {

    const SLUG = "invitationCreate";

    public $PermissionNeed = [];
    public $backMenu;
    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageInvitationMain::SLUG);
    }

    /**
     * @param Player $player
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
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
                $targetName = $data[1];
                $FactionRequest = MainAPI::getFaction($targetName);
                if ($FactionRequest instanceof FactionEntity) {
                    if (count($FactionRequest->members) < $FactionRequest->max_player) {
                        if (!MainAPI::getFactionOfPlayer($Player->getName()) instanceof FactionEntity) {
                            switch ($FactionRequest->visibility) {
                                case Ids::PUBLIC_VISIBILITY:
                                    MainAPI::addMember($FactionRequest->name, $Player->getName());
                                    Utils::newMenuSendTask(new MenuSendTask(
                                        function () use ($Player, $FactionRequest) {
                                            return MainAPI::getUser($Player->getName())->faction === $FactionRequest->name;
                                        },
                                        function () use ($Player, $FactionRequest) {
                                            (new FactionJoinEvent($Player, $FactionRequest))->call();
                                            Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_JOIN_FACTION", ['factionName' => $FactionRequest->name])] );
                                        },
                                        function () use ($Player) {
                                            Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                        }
                                    ));
                                    if (MainAPI::areInInvitation($FactionRequest->name, $Player->getName(), "member")){
                                        MainAPI::removeInvitation($FactionRequest->name, $Player->getName(), "member");
                                    } elseif (MainAPI::areInInvitation($Player->getName(), $FactionRequest->name, "member")){
                                        MainAPI::removeInvitation($Player->getName(), $FactionRequest->name, "member");
                                    }
                                    break;
                                case Ids::PRIVATE_VISIBILITY:
                                    Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "FACTION_DONT_ACCEPT_INVITATION")]);
                                    break;
                                case Ids::INVITATION_VISIBILITY:
                                    if (MainAPI::areInInvitation($targetName, $Player->getName(), InvitationSendEvent::MEMBER_TYPE)) {
                                        MainAPI::addMember($targetName, $Player->getName());
                                        Utils::newMenuSendTask(new MenuSendTask(
                                            function () use ($targetName, $Player) {
                                                return MainAPI::getUser($Player->getName())->faction === $targetName;
                                            },
                                            function () use ($Player, $FactionRequest) {
                                                (new FactionJoinEvent($Player, $FactionRequest))->call();
                                                $Request = MainAPI::$invitation[$FactionRequest->name . "|" . $Player->getName()];
                                                MainAPI::removeInvitation($FactionRequest->name, $Player->getName(), "member");
                                                Utils::newMenuSendTask(new MenuSendTask(
                                                    function () use ($FactionRequest, $Player) {
                                                        return !MainAPI::areInInvitation($FactionRequest->name, $Player->getName(), "member");
                                                    },
                                                    function () use ($Request, $Player) {
                                                        (new InvitationAcceptEvent($Player, $Request))->call();
                                                        Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [Utils::getText($Player->getName(), "SUCCESS_JOIN_FACTION", ['factionName' => $Request->sender])] );
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
                                    }elseif (!MainAPI::areInInvitation($Player->getName(), $targetName, InvitationSendEvent::MEMBER_TYPE)) {
                                        MainAPI::makeInvitation($Player->getName(), $targetName, InvitationSendEvent::MEMBER_TYPE);
                                        Utils::newMenuSendTask(new MenuSendTask(
                                            function () use ($Player, $targetName) {
                                                return MainAPI::areInInvitation($Player->getName(), $targetName, InvitationSendEvent::MEMBER_TYPE);
                                            },
                                            function () use ($Player, $targetName, $backMenu, $data) {
                                                (new InvitationSendEvent($Player, $Player->getName(), $targetName, InvitationSendEvent::MEMBER_TYPE))->call();
                                                Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_SEND_INVITATION", ['name' => $data[1]])] );
                                            },
                                            function () use ($Player) {
                                                Utils::processMenu(RouterFactory::get(self::SLUG), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                            }
                                        ));
                                    }else{
                                        $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_PENDING_INVITATION"));
                                        $Player->sendForm($menu);;
                                    }
                                    break;
                            }
                        }else{
                            $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_IN_THIS_FACTION"));
                            $Player->sendForm($menu);;
                        }
                    }else{
                        $message = Utils::getText($this->UserEntity->name, "MAX_PLAYER_REACH");
                        Utils::processMenu($backMenu, $Player, [$message] );
                    }
                    
                }else{
                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "FACTION_DONT_EXIST"));
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
        $menu->addInput(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_INPUT_CONTENT_FACTION"));
        return $menu;
    }
}