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
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\ConfirmationMenu;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAlliance implements Route {
    
    const SLUG = "manageAlliance";

    public $PermissionNeed = [
        Ids::PERMISSION_BREAK_ALLIANCE
    ];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var FactionEntity */
    private $alliance;

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
        if (!isset($params[0]) || !$params[0] instanceof FactionEntity) throw new InvalidArgumentException("Need the target faction instance");
        $this->alliance = $params[0];

        $this->buttons = [];
        if ((isset($UserPermissions[Ids::PERMISSION_BREAK_ALLIANCE]) && $UserPermissions[Ids::PERMISSION_BREAK_ALLIANCE]) || $User->rank == Ids::OWNER_ID) $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BREAK_ALLIANCE");
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        $menu = $this->manageAlliance();
        $player->sendForm($menu);;
    }

    public function call(): callable
    {
        $alliance = $this->alliance;
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($alliance, $backMenu) {
            if ($data === null) return;
            switch ($this->buttons[$data]) {
                case Utils::getText($this->UserEntity->name, "BUTTON_BACK"):
                    Utils::processMenu($backMenu, $player);
                    break;
                case Utils::getText($this->UserEntity->name, "BUTTON_BREAK_ALLIANCE"):
                    Utils::processMenu(RouterFactory::get(ConfirmationMenu::SLUG), $player, [
                        $this->callKick($alliance->name),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_TITLE_BREAK_ALLIANCE"),
                        Utils::getText($this->UserEntity->name, "CONFIRMATION_CONTENT_BREAK_ALLIANCE")
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
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_ALLIANCE_TITLE", ['name' => $this->alliance->name]));
        return $menu;
    }

    private function callKick(string $targetName) : callable {
        $backMenu = $this->backMenu;
        return function (Player $Player, $data) use ($targetName, $backMenu) {
            $Faction = MainAPI::getFactionOfPlayer($Player->getName());
            if ($data === null) return;
            if ($data) {
                $message = Utils::getText($this->UserEntity->name, "SUCCESS_BREAK_ALLIANCE", ['name' => $targetName]);
                if (!MainAPI::removeAlly($Faction->name, $targetName)) $message = Utils::getText($this->UserEntity->name, "ERROR"); 
                Utils::processMenu($backMenu, $Player, [$message]);
            }else{
                Utils::processMenu(RouterFactory::get(self::SLUG), $Player);
            }
        };
    }
}