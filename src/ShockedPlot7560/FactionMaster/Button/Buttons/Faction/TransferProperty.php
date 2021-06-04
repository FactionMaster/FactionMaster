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

namespace ShockedPlot7560\FactionMaster\Button\Buttons\Faction;

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\FactionPropertyTransferEvent;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Faction\Members\ManageMember as MembersManageMember;
use ShockedPlot7560\FactionMaster\Route\MainPanel;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class TransferProperty extends Button {

    public function __construct(UserEntity $Member)
    {
        parent::__construct(
            "transferProperty", 
            function(string $Player){
                return Utils::getText($Player, "BUTTON_TRANSFER_PROPERTY");
            },  
            function(Player $Player) use ($Member) {
                Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $Player, [
                    function (Player $Player, $data) use ($Member) {
                        if ($data === null) return;
                        if ($data) {
                            $message = Utils::getText($Player->getName(), "SUCCESS_TRANSFER_PROPERTY", ['playerName' => $Member->name]);
                            if (!MainAPI::changeRank($Player->getName(), Ids::COOWNER_ID)) $message = Utils::getText($Player->getName(), "ERROR"); 
                            if (!MainAPI::changeRank($Member->name, Ids::OWNER_ID)) $message = Utils::getText($Player->getName(), "ERROR");
                            (new FactionPropertyTransferEvent($Player, $Member, $Player->getName()))->call();
                            Utils::processMenu(RouterFactory::get(MainPanel::SLUG), $Player, [$message]);
                        }else{
                            Utils::processMenu(RouterFactory::get(MembersManageMember::SLUG), $Player, [$Member]);
                        }
                    },
                    Utils::getText($Player->getName(), "CONFIRMATION_TITLE_TRANSFER_PROPERTY"),
                    Utils::getText($Player->getName(), "CONFIRMATION_CONTENT_TRANSFER_PROPERTY")
                ]);
            }
        );
    }

}