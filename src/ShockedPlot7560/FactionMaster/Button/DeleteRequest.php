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

use ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Event\InvitationRefuseEvent;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class DeleteRequest extends Button {

    public function __construct(InvitationEntity $Request, string $PanelSlug, string $backPanelSlug, array $permissions = []) {
        parent::__construct(
            "deleteRequest",
            function (string $Player) {
                return Utils::getText($Player, "BUTTON_REFUSE_REQUEST");
            },
            function (Player $Player) use ($Request, $PanelSlug, $backPanelSlug) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Request, $PanelSlug, $backPanelSlug) {
                        if ($data === null) {
                            return;
                        }

                        if ($data) {
                            $message = Utils::getText($Player->getName(), "SUCCESS_DELETE_REQUEST", ['name' => $Request->sender]);
                            MainAPI::removeInvitation($Request->sender, $Request->receiver, $Request->type);
                            Utils::newMenuSendTask(new MenuSendTask(
                                function () use ($Request) {
                                    return !MainAPI::areInInvitation($Request->sender, $Request->receiver, $Request->type);
                                },
                                function () use ($Request, $Player, $PanelSlug, $message) {
                                    (new InvitationRefuseEvent($Player, $Request))->call();
                                    Utils::processMenu(RouterFactory::get($PanelSlug), $Player, [$message]);
                                },
                                function () use ($Player, $PanelSlug) {
                                    Utils::processMenu(RouterFactory::get($PanelSlug), $Player, [Utils::getText($Player->getName(), "ERROR")]);
                                }
                            ));
                        } else {
                            Utils::processMenu(RouterFactory::get($backPanelSlug), $Player, [$Request]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_DELETE_REQUEST"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_DELETE_REQUEST"),
                ]);
            },
            $permissions,
            "textures/img/false",
            SimpleForm::IMAGE_TYPE_PATH
        );
    }
}