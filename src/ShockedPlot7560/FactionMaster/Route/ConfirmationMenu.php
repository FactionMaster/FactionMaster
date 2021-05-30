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

use InvalidArgumentException;
use jojoe77777\FormAPI\ModalForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ConfirmationMenu implements Route {

    const SLUG = "confirmationMenu";

    public $PermissionNeed = [];
    public $backMenu;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
    }

    /**
     * @param Player $player 
     * @param array|null $params 
     *      Give to first item of the list, a callable to call when the confirmation are send
     *      At the second, the title
     *      At the third, the content
     *      (Optionnal) at the fourth, array with for the first item -> message for positive answer and second the negative
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0])) throw new InvalidArgumentException("First item must be set");

        $this->backMenu = $params[0];

        $menu = $this->confirmationMenu($params);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        return $this->backMenu;
    }

    private function confirmationMenu(array $params) : ModalForm {
        $menu = new ModalForm($this->call());
        $menu->setTitle($params[1]);
        $menu->setContent($params[2]);
        $menu->setButton1(isset($params[3]) ? $params[3] : Utils::getText($this->UserEntity->name, "BUTTON_MODAL_YES"));
        $menu->setButton2(isset($params[4]) ? $params[4] : Utils::getText($this->UserEntity->name, "BUTTON_MODAL_NO"));
        return $menu;
    }

}