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

namespace ShockedPlot7560\FactionMaster;

use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\item\Bucket;
use pocketmine\item\Hoe;
use pocketmine\item\ItemIds;
use pocketmine\item\Shovel;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class EventListener implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;
    }

    public function onBreak(BlockBreakEvent $event) {
        $Block = $event->getBlock();
        $level = $Block->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
            $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction !== $factionPlayer->name)) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_CLAIM"));
                return;
            }
        }
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

    public function onDamage(EntityDamageByEntityEvent $event) {
        $Victim = $event->getEntity();
        $Damager = $event->getDamager();
        if ($Victim instanceof Player && $Damager instanceof Player) {
            $Victim = $Victim->getPlayer()->getName();
            $Damager = $Damager->getPlayer()->getName();
            if (MainAPI::sameFaction($Victim, $Damager)) {
                $event->setCancelled(true);
            }
            $VictimFaction = MainAPI::getFactionOfPlayer($Victim);
            $DamagerFaction = MainAPI::getFactionOfPlayer($Damager);
            if ($DamagerFaction instanceof FactionEntity && $VictimFaction instanceof FactionEntity && MainAPI::isAlly($DamagerFaction->name, $VictimFaction->name)) {
                $event->setCancelled(true);
            }
        }else{
            return true;
        }
    }

    public function onDeath(PlayerDeathEvent $event) {
        $Entity = $event->getEntity();
        $cause = $Entity->getLastDamageCause();
        $config = Main::getInstance()->config;

        if ($cause instanceof EntityDamageByEntityEvent) {
            $Damager = $cause->getDamager();
            if ($Damager instanceof Player) {
                $victimInventoryArmor = $Entity->getPlayer()->getArmorInventory();

                if (!$config->get('allow-no-stuff')) {
                    if ($victimInventoryArmor->getHelmet()->getId() == ItemIds::AIR
                        && $victimInventoryArmor->getChestplate()->getId() == ItemIds::AIR
                        && $victimInventoryArmor->getLeggings()->getId() == ItemIds::AIR
                        && $victimInventoryArmor->getBoots()->getId() == ItemIds::AIR) return;
                }
                
                $DamagerName = $Damager->getPlayer()->getName();
                $VictimName = $Entity->getPlayer()->getName();

                $VictimFaction = MainAPI::getFactionOfPlayer($VictimName);
                $DamagerFaction = MainAPI::getFactionOfPlayer($DamagerName);
                if ($DamagerFaction instanceof FactionEntity) {
                    if ($VictimFaction instanceof FactionEntity) {
                        $PowerDamager = $config->get("power-win-per-kill") * $config->get("faction-multiplicator");
                        $PowerVictim = $config->get('power-loose-per-kill') * -1 * $config->get("faction-multiplicator");
                    }else{
                        $PowerDamager = $config->get("power-win-per-kill");
                    }
                }elseif ($VictimFaction instanceof FactionEntity) {
                    $PowerVictim = $config->get("power-loose-per-death") * -1;
                }
                if (isset($PowerDamager) && $DamagerFaction instanceof FactionEntity) MainAPI::changePower($DamagerFaction->name, $PowerDamager);
                if (isset($PowerVictim) && $VictimFaction instanceof FactionEntity) MainAPI::changePower($VictimFaction->name, $PowerVictim);
                if ($DamagerFaction instanceof FactionEntity) MainAPI::addXP($DamagerFaction->name, 1);
            }
        }
    }

    public function onInteract(PlayerInteractEvent $event) {
        $Block = $event->getBlock();
        $Item = $event->getItem();
        $Player = $event->getPlayer();
        $level = $Player->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (!$Chunk instanceof Chunk) return;
        if ($Item instanceof Hoe || $Item instanceof Shovel || $Item instanceof Bucket ||
            $Block instanceof Chest || $Block instanceof Door || $Block instanceof Trapdoor || 
            $Block instanceof FenceGate || $Block instanceof Furnace || $Block instanceof ItemFrame) {
                if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
                    $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
                    if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction !== $factionPlayer->name)) {
                        $event->setCancelled(true);
                        $event->getPlayer()->sendMessage(Utils::getText($Player->getName(), "CANT_INTERACT_CLAIM"));
                        return;
                    }
                }
        }
    }

    public function onJoin(PlayerLoginEvent $event) {
        $playerName = $event->getPlayer()->getName();
        $UserEntity = MainAPI::getUser($playerName);
        if ($UserEntity === null) {
            MainAPI::$languages[$playerName] = Utils::getConfigLang("default-language");
            if (!MainAPI::addUser($playerName)) {
                $event->setKickMessage(Utils::getText($event->getPlayer()->getName(), "ERROR_DATA_SAVING"));
                $event->setCancelled(true);
            }
        }else{
            MainAPI::$languages[$playerName] = $UserEntity->language;
        }
        return;
    }
}