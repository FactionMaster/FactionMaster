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

use jojoe77777\FormAPI\CustomForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class NewAllianceInvitation implements Route {

    const SLUG = "allianceInvitationCreate";

    public $PermissionNeed = [
        Ids::PERMISSION_SEND_ALLIANCE_INVITATION
    ];
    public $backMenu;

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
            $FactionRequest = MainAPI::getFaction($data[1]);

            if ($data[1] !== "") {
                if ($FactionRequest instanceof FactionEntity) {
                    $FactionPlayer = MainAPI::getFactionOfPlayer($Player->getName());
                    if (count($FactionPlayer->ally) < $FactionPlayer->max_ally) {
                        if (count($FactionRequest->ally) < $FactionRequest->max_ally) {
                            if (!MainAPI::areInInvitation($this->Faction->name, $data[1], "alliance")) {
                                if (MainAPI::makeInvitation($this->Faction->name, $data[1], "alliance")) {
                                    Utils::processMenu($backMenu, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_SEND_INVITATION", ['name' => $data[1]])] );
                                }else{
                                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ERROR"));
                                    $Player->sendForm($menu);
                                }
                            }else{
                                $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "ALREADY_PENDING_INVITATION"));
                                $Player->sendForm($menu);
                            }
                        }else{
                            $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "MAX_ALLY_REACH_OTHER"));
                            $Player->sendForm($menu);
                        }
                    }else{
                        $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "MAX_ALLY_REACH"));
                        $Player->sendForm($menu);
                    }
                }else{
                    $menu = $this->createInvitationMenu(Utils::getText($this->UserEntity->name, "FACTION_DONT_EXIST"));
                    $Player->sendForm($menu);
                } 
            }else{
                Utils::processMenu($backMenu, $Player);
            }
        };
    }

    private function createInvitationMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_TITLE"));
        $menu->addLabel(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_CONTENT") . "\n§r" . $message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "SEND_INVITATION_PANEL_INPUT_CONTENT_FACTION"));
        return $menu;
    }
}