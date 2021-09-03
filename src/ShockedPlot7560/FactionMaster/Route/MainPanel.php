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
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\Collection;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionFac;
use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionNoFac;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainPanel implements Route {

    const SLUG = "main";

    const NO_FACTION_TYPE = 0;
    const FACTION_TYPE = 1;

    public $PermissionNeed = [];

    /** @var UserEntity */
    private $UserEntity;
    /** @var int */
    private $menuType;
    /** @var Collection */
    private $Collection;

    public function getSlug(): string {
        return self::SLUG;
    }

    public function __invoke(Player $Player, UserEntity $User, array $UserPermissions, ?array $params = null) {
        $this->UserEntity = $User;
        $message = '';
        if (isset($params[0])) {
            $message = $params[0];
        }

        if ($this->UserEntity->faction === null) {
            $this->Collection = CollectionFactory::get(MainCollectionNoFac::SLUG)->init($Player, $User);
            $this->menuType = self::NO_FACTION_TYPE;
            $menu = $this->noFactionMenu($message);
        } else {
            $this->Collection = CollectionFactory::get(MainCollectionFac::SLUG)->init($Player, $User);
            $this->menuType = self::FACTION_TYPE;
            $menu = $this->factionMenu($message);
        }
        $Player->sendForm($menu);
    }

    public function call(): callable {
        $Collection = $this->Collection;
        return function (Player $Player, $data) use ($Collection) {
            if ($data === null) {
                return;
            }

            switch ($this->menuType) {
            case self::NO_FACTION_TYPE:
                $Collection->process($data, $Player);
                break;
            case self::FACTION_TYPE:
                $Collection->process($data, $Player);
                break;
            default:
                return;
            }
        };
    }

    private function noFactionMenu(string $message = ""): SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = $this->Collection->generateButtons($menu, $this->UserEntity->name);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MAIN_PANEL_TITLE_NO_FACTION"));
        if ($message !== "") {
            $menu->setContent($message);
        }

        return $menu;
    }

    private function factionMenu(string $message = ""): SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = $this->Collection->generateButtons($menu, $this->UserEntity->name);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MAIN_PANEL_TITLE_HAVE_FACTION", ["factionName" => $this->UserEntity->faction]));
        if ($message !== "") {
            $menu->setContent($message);
        }

        return $menu;
    }
}