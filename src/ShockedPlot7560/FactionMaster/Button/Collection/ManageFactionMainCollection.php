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

namespace ShockedPlot7560\FactionMaster\Button\Collection;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Button\Back;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\AllianceMainMenu;
use ShockedPlot7560\FactionMaster\Route\ChangeDescription;
use ShockedPlot7560\FactionMaster\Route\ChangeMessage;
use ShockedPlot7560\FactionMaster\Route\ChangePermissionMain;
use ShockedPlot7560\FactionMaster\Route\ChangeVisibility;
use ShockedPlot7560\FactionMaster\Route\LevelUp;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageFactionMainCollection extends Collection {

    const SLUG = "manageFactionMain";

    public function __construct()
    {
        parent::__construct(self::SLUG);
        $this->registerCallable(self::SLUG, function(Player $player, UserEntity $user) {
            $this->register(new Button(
                "changeDescription", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_CHANGE_DESCRIPTION");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(ChangeDescription::SLUG), $Player);
                },
                [ PermissionIds::PERMISSION_CHANGE_FACTION_DESCRIPTION ]
            ));
            $this->register(new Button(
                "changeMessage", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_CHANGE_MESSAGE");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(ChangeMessage::SLUG), $Player);
                },
                [ PermissionIds::PERMISSION_CHANGE_FACTION_MESSAGE ]
            ));
            $this->register(new Button(
                "changeVisibility", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_CHANGE_VISIBILITY");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(ChangeVisibility::SLUG), $Player);
                },
                [ PermissionIds::PERMISSION_CHANGE_FACTION_VISIBILITY ]
            ));
            $this->register(new Button(
                "levelUp", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_LEVEL_UP");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(LevelUp::SLUG), $Player);
                },
                [
                    PermissionIds::PERMISSION_LEVEL_UP
                ]
            ));
            $this->register(new Button(
                "changePermission", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_CHANGE_PERMISSION");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(ChangePermissionMain::SLUG), $Player);
                },
                [
                    PermissionIds::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS
                ]
            ));
            $this->register(new Button(
                "manageAlliance", 
                function($Player) {
                    return Utils::getText($Player, "BUTTON_MANAGE_ALLIANCE");
                }, 
                function(Player $Player) {
                    Utils::processMenu(RouterFactory::get(AllianceMainMenu::SLUG), $Player);
                },
                [
                    PermissionIds::PERMISSION_SEND_ALLIANCE_INVITATION,
                    PermissionIds::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION,
                    PermissionIds::PERMISSION_ACCEPT_ALLIANCE_DEMAND,
                    PermissionIds::PERMISSION_REFUSE_ALLIANCE_DEMAND
                ]
            ));
            $this->register(new Back(MainPanel::SLUG));
        });
    }
}