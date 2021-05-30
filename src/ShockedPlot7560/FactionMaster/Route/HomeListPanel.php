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

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Route\Route;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeListPanel implements Route {

    const SLUG = "homeListPanel";

    public $PermissionNeed = [Ids::PERMISSION_TP_FACTION_HOME];
    public $backMenu;

    /** @var array */
    private $buttons;
    /** @var array[] */
    private $Homes;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $message = "";
        $Faction = MainAPI::getFactionOfPlayer($player->getName());
        $this->UserEntity = $User;

        $this->buttons = [];
        $this->Homes = MainAPI::getFactionHomes($Faction->name);
        $i = 0;
        foreach ($this->Homes as $Name => $Home) {
            $Home['name'] = $Name;
            $this->Homes[$i] = $Home;
            $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_LISTING_HOME", [
                'name' => $Name,
                'x' => $Home['x'],
                'y' => $Home['y'],
                'z' => $Home['z']
            ]);
            $i++;
        }
        $this->buttons[] = Utils::getText($this->UserEntity->name, "BUTTON_BACK");

        if (isset($params[0])) $message = $params[0];
        if (count($Faction->members) == 0) $message .= Utils::getText($this->UserEntity->name, "NO_HOME_SET");
        
        $menu = $this->manageMembersListMenu($message);
        $player->sendForm($menu);
    }

    public function call(): callable
    {
        $backMenu = $this->backMenu;
        return function (Player $player, $data) use ($backMenu) {
            if ($data === null) return;
            if ($data == count($this->buttons) - 1) {
                Utils::processMenu($backMenu, $player);
                return;
            }
            if (isset($this->Homes[$data])) {
                $Home = $this->Homes[$data];
                $player->teleport(new Vector3($Home["x"], $Home["y"], $Home['z']));
                $player->sendMessage(Utils::getText($this->UserEntity->name, "SUCCESS_TELEPORT_HOME"));
            }
            return;
        };
    }

    private function manageMembersListMenu(string $message = "") : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = Utils::generateButton($menu, $this->buttons);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "HOME_FACTION_PANEL_TITLE"));
        $content = Utils::getText($this->UserEntity->name, "HOME_FACTION_PANEL_CONTENT");
        if ($message !== "") $content .= ("\n§r" . $message);
        $menu->setContent($content);
        return $menu;
    }

}