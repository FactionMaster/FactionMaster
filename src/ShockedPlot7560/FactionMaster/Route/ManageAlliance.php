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
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Collection\CollectionFactory;
use ShockedPlot7560\FactionMaster\Button\Collection\ManageAllianceCollection;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageAlliance implements Route {
    
    const SLUG = "manageAlliance";

    /** @var \ShockedPlot7560\FactionMaster\Button\Collection\Collection */
    private $Collection;
    /** @var FactionEntity */
    private $alliance;
    /** @var UserEntity */
    private $UserEntity;

    public function getSlug(): string
    {
        return self::SLUG;
    }

    public function __invoke(Player $player, UserEntity $User, array $UserPermissions, ?array $params = null)
    {
        $this->UserEntity = $User;
        if (!isset($params[0]) || !$params[0] instanceof FactionEntity) throw new InvalidArgumentException("Need the target faction instance");
        $this->alliance = $params[0];

        $this->Collection = CollectionFactory::get(ManageAllianceCollection::SLUG)->init($player, $User, $this->alliance);

        $menu = $this->manageAlliance();
        $player->sendForm($menu);;
    }

    public function call(): callable
    {
        $Collection = $this->Collection;
        return function (Player $player, $data) use ($Collection) {
            if ($data === null) return;
            $Collection->process($data, $player);
        };
    }

    private function manageAlliance() : SimpleForm {
        $menu = new SimpleForm($this->call());
        $menu = $this->Collection->generateButtons($menu, $this->UserEntity->name);
        $menu->setTitle(Utils::getText($this->UserEntity->name, "MANAGE_ALLIANCE_TITLE", ['name' => $this->alliance->name]));
        return $menu;
    }
}