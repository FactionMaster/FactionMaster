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

namespace ShockedPlot7560\FactionMaster\Button;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Event\InvitationDeleteEvent;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DeleteInvitation extends Button {

    public function __construct(InvitationEntity $Invitation, string $PanelSlug, array $permissions = [])
    {
        parent::__construct(
            "deleteInvitation", 
            function(string $Player) {
                return Utils::getText($Player, "BUTTON_DELETE_INVITATION");
            },  
            function(Player $Player) use ($Invitation, $PanelSlug) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Invitation, $PanelSlug) {
                        if ($data === null) return;
                        if ($data) {
                            $message = Utils::getText($Player->getName(), "SUCCESS_DELETE_INVITATION", ['name' => $Invitation->receiver]);
                            MainAPI::removeInvitation($Invitation->sender, $Invitation->receiver, $Invitation->type);
                            Utils::newMenuSendTask(new MenuSendTask(
                                function () use ($Invitation) {
                                    return !MainAPI::areInInvitation($Invitation->sender, $Invitation->receiver, $Invitation->type);
                                },
                                function () use ($Invitation, $Player, $PanelSlug, $message) {
                                    (new InvitationDeleteEvent($Player, $Invitation))->call();
                                    Utils::processMenu(RouterFactory::get($PanelSlug), $Player, [$message]);
                                },
                                function () use ($Player, $PanelSlug) {
                                    Utils::processMenu(RouterFactory::get($PanelSlug), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                }
                            ));
                        }else{
                            Utils::processMenu(RouterFactory::get($PanelSlug), $Player, [$Invitation]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_DELETE_INVITATION"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_DELETE_INVITATION")
                ]);
            },
            $permissions
        );
    }
}