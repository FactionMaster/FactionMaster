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
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\VisibilityChangeEvent;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ChangeVisibility implements Route {

    const SLUG = "changeVisibility";

    public $PermissionNeed = [
        PermissionIds::PERMISSION_CHANGE_FACTION_VISIBILITY
    ];
    public $backMenu;

    /** @var array */
    private $sliderData;
    /** @var FactionEntity */
    private $Faction;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(ManageFactionMain::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $this->sliderData = [
            Ids::PUBLIC_VISIBILITY => Utils::getText($this->UserEntity->name, "PUBLIC_VISIBILITY_NAME"),
            Ids::PRIVATE_VISIBILITY => Utils::getText($this->UserEntity->name, "PRIVATE_VISIBILITY_NAME"),
            Ids::INVITATION_VISIBILITY => Utils::getText($this->UserEntity->name, "INVITATION_VISIBILITY_NAME")
        ];
        $this->Faction = MainAPI::getFactionOfPlayer($player->getName());
        
        $menu = $this->changeVisibility();
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        $Faction = $this->Faction;
        return function (Player $player, $data) use ($backMenu, $Faction) {
            if ($data === null) return;
            if (MainAPI::changeVisibility($this->Faction->name, $data[0])) {
                (new VisibilityChangeEvent($player, $Faction, $data[0]))->call();
                Utils::processMenu($backMenu,  $player, [Utils::getText($this->UserEntity->name, "SUCCESS_VISIBILITY_UPDATE")]);
            }else{
                Utils::processMenu($backMenu, $player, [Utils::getText($this->UserEntity->name, "ERROR")]);
            }
        };
    }

    private function changeVisibility() : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->addStepSlider(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_STEP"), $this->sliderData, $this->Faction->visibility);
        $menu->addLabel(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_EXPLICATION"));
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CHANGE_VISIBILITY_TITLE"));
        return $menu;
    }
}