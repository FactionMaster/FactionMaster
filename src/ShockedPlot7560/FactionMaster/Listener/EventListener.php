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

namespace ShockedPlot7560\FactionMaster\Listener;

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
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\item\Bucket;
use pocketmine\item\Hoe;
use pocketmine\item\ItemIds;
use pocketmine\item\Shovel;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\ClaimEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Task\MenuSendTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class EventListener implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main) {
        $this->Main = $Main;
    }

    public function onBreak(BlockBreakEvent $event): void {
        $Block = $event->getBlock();
        $level = $Block->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
            $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction->faction !== $factionPlayer->name)) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_BREAK_CLAIM"));
                return;
            }
        }
    }

    public function onPlace(BlockPlaceEvent $event): void {
        $Block = $event->getBlock();
        $level = $Block->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
            $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction->faction !== $factionPlayer->name)) {
                $event->setCancelled(true);
                $event->getPlayer()->sendMessage(Utils::getText($event->getPlayer()->getName(), "CANT_PLACE_CLAIM"));
                return;
            }
        }
    }

    public function onDamage(EntityDamageByEntityEvent $event): void {
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
        } else {
            return;
        }
    }

    public function onDeath(PlayerDeathEvent $event): void {
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
                        && $victimInventoryArmor->getBoots()->getId() == ItemIds::AIR) {
                        return;
                    }

                }

                $DamagerName = $Damager->getPlayer()->getName();
                $VictimName = $Entity->getPlayer()->getName();

                $VictimFaction = MainAPI::getFactionOfPlayer($VictimName);
                $DamagerFaction = MainAPI::getFactionOfPlayer($DamagerName);
                if ($DamagerFaction instanceof FactionEntity) {
                    if ($VictimFaction instanceof FactionEntity) {
                        $PowerDamager = $config->get("power-win-per-kill") * $config->get("faction-multiplicator");
                        $PowerVictim = $config->get('power-loose-per-kill') * -1 * $config->get("faction-multiplicator");
                    } else {
                        $PowerDamager = $config->get("power-win-per-kill");
                    }
                } elseif ($VictimFaction instanceof FactionEntity) {
                    $PowerVictim = $config->get("power-loose-per-death") * -1;
                }
                if (isset($PowerDamager) && $DamagerFaction instanceof FactionEntity) {
                    MainAPI::changePower($DamagerFaction->name, $PowerDamager);
                }

                if (isset($PowerVictim) && $VictimFaction instanceof FactionEntity) {
                    MainAPI::changePower($VictimFaction->name, $PowerVictim);
                }

                if ($DamagerFaction instanceof FactionEntity) {
                    MainAPI::addXP($DamagerFaction->name, 1);
                }

            }
        }
    }

    public function onInteract(PlayerInteractEvent $event): void {
        $Block = $event->getBlock();
        $Item = $event->getItem();
        $Player = $event->getPlayer();
        $level = $Player->getLevel();
        $Chunk = $level->getChunkAtPosition(new Vector3($Block->getX(), $Block->getY(), $Block->getZ()));

        if (!$Chunk instanceof Chunk) {
            return;
        }

        if ($Item instanceof Hoe || $Item instanceof Shovel || $Item instanceof Bucket ||
            $Block instanceof Chest || $Block instanceof Door || $Block instanceof Trapdoor ||
            $Block instanceof FenceGate || $Block instanceof Furnace || $Block instanceof ItemFrame) {
            if (($faction = MainAPI::getFactionClaim($level->getName(), $Chunk->getX(), $Chunk->getZ())) !== null) {
                $factionPlayer = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
                if (!$factionPlayer instanceof FactionEntity || ($factionPlayer instanceof FactionEntity && $faction->faction !== $factionPlayer->name)) {
                    $event->setCancelled(true);
                    $event->getPlayer()->sendMessage(Utils::getText($Player->getName(), "CANT_INTERACT_CLAIM"));
                    return;
                }
            }
        }
    }

    public function onJoin(PlayerLoginEvent $event): void {
        $playerName = $event->getPlayer()->getName();
        $UserEntity = MainAPI::getUser($playerName);
        if ($UserEntity === null) {
            MainAPI::$languages[$playerName] = Utils::getConfigLang("default-language");
            MainAPI::addUser($playerName);
        } else {
            MainAPI::$languages[$playerName] = $UserEntity->language;
        }
        Utils::newMenuSendTask(new MenuSendTask(
            function () use ($playerName) {
                return MainAPI::getUser($playerName) instanceof UserEntity;
            },
            function () use ($playerName) {
                $user = MainAPI::getUser($playerName);
                if ($user->faction !== null) {
                    Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                        new DatabaseTask(
                            "SELECT * FROM " . FactionTable::TABLE_NAME . " WHERE name = :name",
                            [
                                "name" => $user->faction,
                            ],
                            function ($result) use ($user) {
                                $faction = $result[0];
                                MainAPI::$factions[$user->faction] = $faction;
                            },
                            FactionEntity::class
                        ));
                }
                Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                    new DatabaseTask(
                        "SELECT * FROM " . InvitationTable::TABLE_NAME . " WHERE sender = :name OR receiver = :name" . ($user->faction !== null ? " OR sender = :factionName OR receiver = :factionName" : ""),
                        ($user->faction !== null ? [
                            "name" => $playerName,
                            "factionName" => $user->faction,
                        ] : [
                            "name" => $playerName,
                        ]),
                        function ($result) {
                            foreach ($result as $invitation) {
                                MainAPI::$invitation[$invitation->sender . "|" . $invitation->receiver] = $invitation;
                            }
                        },
                        InvitationEntity::class
                    ));
                Main::getInstance()->getServer()->getAsyncPool()->submitTask(
                    new DatabaseTask(
                        "SELECT * FROM " . UserTable::TABLE_NAME . " WHERE name = :name",
                        [
                            "name" => $playerName,
                        ],
                        function ($result) use ($playerName) {
                            MainAPI::$users[$playerName] = $result[0];
                        },
                        UserEntity::class
                    ));
            },
            function () use ($event) {
                $event->getPlayer()->kick(Utils::getText($event->getPlayer()->getName(), "ERROR_DATA_SAVING"), false);
            }
        ));
        return;
    }

    public function onMove(PlayerMoveEvent $event): void {
        if (Utils::getConfig("message-alert") === true) {
            if (!isset(Main::$activeTitle[$event->getPlayer()->getName()]) 
                    || (time() - Main::$activeTitle[$event->getPlayer()->getName()]) > (int)Utils::getConfig("message-alert-cooldown")) {
                $to = $event->getTo();
                $claim = $to->getLevel()->getChunkAtPosition(new Vector3($to->getX(), $to->getY(), $to->getZ()));
                $claim = MainAPI::getFactionClaim($to->getLevel()->getName(), $claim->getX(), $claim->getZ());
                if ($claim instanceof ClaimEntity) {
                    $title = str_replace("{factionName}", $claim->faction, Utils::getConfig("message-alert-title"));
                    $subtitle = str_replace("{factionName}", $claim->faction, Utils::getConfig("message-alert-subtitle"));
                    $event->getPlayer()->sendTitle(
                        $title,
                        $subtitle
                    );
                    Main::$activeTitle[$event->getPlayer()->getName()] = time();
                }                
            }
        }
    }

    public function onChat(PlayerChatEvent $event): void {
        if (mb_substr(trim($event->getMessage()), 0, 1) === Utils::getConfig("faction-chat-symbol") && Utils::getConfig("faction-chat-active") === true) {
            $Faction = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if ($Faction instanceof FactionEntity) {
                $message = str_replace(
                    [ "{factionName}", "{playerName}", "{message}" ],
                    [ $Faction->name, $event->getPlayer()->getName(), substr(trim($event->getMessage()), 1, strlen(trim($event->getMessage())))],
                    Utils::getConfig("faction-chat-message")
                );
                foreach ($Faction->members as $name => $rank) {
                    $player = $this->Main->getServer()->getPlayer($name);
                    if ($player instanceof Player) {
                        $player->sendMessage($message);
                    }
                }
                $event->setCancelled(true);
            }
        }elseif (mb_substr(trim($event->getMessage()), 0, 1) === Utils::getConfig("ally-chat-symbol") && Utils::getConfig("ally-chat-active") === true) {
            $Faction = MainAPI::getFactionOfPlayer($event->getPlayer()->getName());
            if ($Faction instanceof FactionEntity) {
                $message = str_replace(
                    [ "{factionName}", "{playerName}", "{message}" ],
                    [ $Faction->name, $event->getPlayer()->getName(), substr(trim($event->getMessage()), 1, strlen(trim($event->getMessage())))],
                    Utils::getConfig("ally-chat-message")
                );
                foreach ($Faction->ally as $Name) {
                    $ally = MainAPI::getFaction($Name);
                    if ($ally instanceof FactionEntity) {
                        foreach ($ally->members as $name => $rank) {
                            $player = $this->Main->getServer()->getPlayer($name);
                            if ($player instanceof Player) {
                                $player->sendMessage($message);
                            }
                        }
                    }
                }
                foreach ($Faction->members as $name => $rank) {
                    $player = $this->Main->getServer()->getPlayer($name);
                    if ($player instanceof Player) {
                        $player->sendMessage($message);
                    }
                }
                $event->setCancelled(true);
            }
        }
    }
}