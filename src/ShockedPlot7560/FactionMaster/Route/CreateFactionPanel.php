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
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Router\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class CreateFactionPanel implements Route {

    const SLUG = "createFaction";

    public $PermissionNeed = [];
    public $backMenu;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __construct()
    {
        $this->backMenu = RouterFactory::get(MainPanel::SLUG);
    }

    /**
     * @param array|null $params Give to first item the message to print if wanted
     */
    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        $message = "";
        if (isset($params[0]) && \is_string($params[0])) $message = $params[0];
        $menu = $this->createFactionMenu($message);
        $player->sendForm($menu);;
    }

    public function call() : callable{
        $backRoute = $this->backMenu;
        return function (Player $Player, $data) use ($backRoute) {
            if ($data === null) return;
            $FactionRequest = MainAPI::getFaction($data[1]);
            if ($data[1] !== "") {
                if (!$FactionRequest instanceof FactionEntity) {
                    if (\strlen($data[1]) >= Main::getInstance()->config->get("min-faction-name-length")
                        && \strlen($data[1]) <= Main::getInstance()->config->get("max-faction-name-length")) {
                        if (MainAPI::addFaction($data[1], $Player->getName())) {
                            Utils::processMenu($backRoute, $Player, [Utils::getText($this->UserEntity->name, "SUCCESS_CREATE_FACTION")]);
                        }else{
                            $menu = $this->createFactionMenu(Utils::getText($this->UserEntity->name, "ERROR"));
                            $Player->sendForm($menu);
                        }
                    }else{
                        $menu = $this->createFactionMenu(Utils::getText($this->UserEntity->name, "MAX_MIN_REACH_NAME"));
                        $Player->sendForm($menu);
                    }
                }else{
                    $menu = $this->createFactionMenu(Utils::getText($this->UserEntity->name, "FACTION_NAME_ALREADY_EXIST"));
                    $Player->sendForm($menu);
                } 
            }else{
                Utils::processMenu($backRoute, $Player);

            }
        };
    }

    private function createFactionMenu(string $message = "") : CustomForm {
        $menu = new CustomForm($this->call());
        $menu->setTitle(Utils::getText($this->UserEntity->name, "CREATE_FACTION_PANEL_TITLE"));
        $menu->addLabel(Utils::getText($this->UserEntity->name, "CREATE_FACTION_PANEL_CONTENT") . "\n".$message);
        $menu->addInput(Utils::getText($this->UserEntity->name, "CREATE_FACTION_PANEL_INPUT_CONTENT"));
        return $menu;
    }
}