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

use ShockedPlot7560\FactionMaster\libs\jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\MainFacCollection;
use ShockedPlot7560\FactionMaster\Button\Collection\MainNoFacCollection;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class MainRoute extends RouteBase implements Route {

    const SLUG = "mainRoute";

    const NO_FACTION_TYPE = 0;
    const FACTION_TYPE = 1;

    /** @var int */
    private $menuType;

    public function getSlug(): string {
        return self::SLUG;
    }

    public function getPermissions(): array {
        return [];
    }

    public function getBackRoute(): ?Route {
        return null;
    }

    protected function getMenuType(): int {
        return $this->menuType;
    }

    public function __invoke(Player $player, UserEntity $userEntity, array $userPermissions, ?array $params = null) {
        $this->init($player, $userEntity, $userPermissions, $params);

        $message = '';
        if (isset($params[0])) {
            $message = $params[0];
        }

        if ($this->getUserEntity()->getFactionEntity() instanceof FactionEntity) {
            $this->setCollection(CollectionFactory::get(MainFacCollection::SLUG)->init($this->getPlayer(), $this->getUserEntity()));
            $this->menuType = self::FACTION_TYPE;
        } else {
            $this->setCollection(CollectionFactory::get(MainNoFacCollection::SLUG)->init($this->getPlayer(), $this->getUserEntity()));
            $this->menuType = self::NO_FACTION_TYPE;
        }
        $this->getPlayer()->sendForm($this->getForm($message));
    }

    public function call(): callable {
        return function (Player $player, $data) {
            if ($data === null) {
                return;
            }
            $this->getCollection()->process($data, $player);
            return;
        };
    }

    protected function getForm(string $message = ""): ?SimpleForm {
        switch ($this->getMenuType()) {
            case self::FACTION_TYPE:
                $menu = new SimpleForm($this->call());
                $menu = $this->getCollection()->generateButtons($menu, $this->getUserEntity()->getName());
                $menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "MAIN_PANEL_TITLE_HAVE_FACTION", ["factionName" => $this->getUserEntity()->getFactionName()]));
                if ($message !== "") {
                    $menu->setContent($message);
                }                
                return $menu;
            
            case self::NO_FACTION_TYPE:
                $menu = new SimpleForm($this->call());
                $menu = $this->getCollection()->generateButtons($menu, $this->getUserEntity()->getName());
                $menu->setTitle(Utils::getText($this->getUserEntity()->getName(), "MAIN_PANEL_TITLE_NO_FACTION"));
                if ($message !== "") {
                    $menu->setContent($message);
                }
                return $menu;
        }
        return null;
    }
}