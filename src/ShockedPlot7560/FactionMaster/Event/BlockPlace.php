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

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class BlockPlace implements Listener {

    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;   
    }

    public function onPlace(BlockPlaceEvent $event) {
        $Block = $event->getBlock();
        $level = $Block->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
            $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction !== $factionPlayer->name)) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_CLAIM"));
                return;
            }
        }
    }

}