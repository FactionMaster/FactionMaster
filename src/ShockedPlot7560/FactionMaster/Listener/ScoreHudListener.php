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

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\event\TagsResolveEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\event\Listener;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Event\DescriptionChangeEvent;
use ShockedPlot7560\FactionMaster\Event\FactionCreateEvent;
use ShockedPlot7560\FactionMaster\Event\FactionDeleteEvent;
use ShockedPlot7560\FactionMaster\Event\FactionJoinEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLeaveEvent;
use ShockedPlot7560\FactionMaster\Event\FactionLevelChangeEvent;
use ShockedPlot7560\FactionMaster\Event\FactionPowerEvent;
use ShockedPlot7560\FactionMaster\Event\FactionPropertyTransferEvent;
use ShockedPlot7560\FactionMaster\Event\FactionXPChangeEvent;
use ShockedPlot7560\FactionMaster\Event\MemberChangeRankEvent;
use ShockedPlot7560\FactionMaster\Event\MessageChangeEvent;
use ShockedPlot7560\FactionMaster\Event\VisibilityChangeEvent;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ScoreHudListener implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main) {
        $this->Main = $Main;
    }

    public function onTagResolve(TagsResolveEvent $event): void {
        $player = $event->getPlayer();
        $tag = $event->getTag();
        switch ($tag->getName()) {
            case Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->description ?? "");
                }else{
                    $tag->setValue("");
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_LEVEL:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->level);
                }else{
                    $tag->setValue(0);
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_MESSAGE:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->messageFaction ?? "");
                }else{
                    $tag->setValue("");
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_NAME:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->name);
                }else{
                    $tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_POWER:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->power);
                }else{
                    $tag->setValue(0);
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    switch ($faction->visibility) {
                        case Ids::PUBLIC_VISIBILITY:
                            $visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
                            break;
                        case Ids::PRIVATE_VISIBILITY:
                            $visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
                            break;
                        case Ids::INVITATION_VISIBILITY:
                            $visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
                            break;
                        default:
                            $visibility = "Unknow";
                            break;
                    }
                    $tag->setValue($visibility);
                }else{
                    $tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));
                }
                break;
            case Ids::HUD_FACTIONMASTER_FACTION_XP:
                $faction = MainAPI::getFactionOfPlayer($player->getName());
                if ($faction instanceof FactionEntity) {
                    $tag->setValue($faction->xp);
                }else{
                    $tag->setValue(0);
                }
                break;
            case Ids::HUD_FACTIONMASTER_PLAYER_RANK:
                $User = MainAPI::getUser($player->getName());
                if ($User instanceof UserEntity) {
                    if ($User->rank !== null && $User->faction !== null) {
                        switch ($User->rank) {
                            case Ids::RECRUIT_ID:
                                $rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
                                break;
                            case Ids::MEMBER_ID:
                                $rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
                                break;
                            case Ids::COOWNER_ID:
                                $rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
                                break;
                            case Ids::OWNER_ID:
                                $rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
                                break;
                            default: 
                                $rank = "Unknow";
                                break;
                        }
                        $tag->setValue($rank);                    
                    } else {
                        $tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));                    
                    }
                } else {
                    $tag->setValue(Utils::getText($player->getName(), "NO_FACTION_TAG"));                    
                }
                break;
        }
    }

    public function onFactionCreate(FactionCreateEvent $event): void {
        $player = $event->getPlayer();
        $Faction = MainAPI::getFaction($event->getFaction());
        if ($Faction instanceof FactionEntity) {
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_NAME,
                $Faction->name
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_POWER,
                $Faction->power
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
                $Faction->level
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_XP,
                $Faction->xp
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
                $Faction->messageFaction ?? ""
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
                $Faction->description ?? ""
            ));
            switch ($Faction->visibility) {
                case Ids::PUBLIC_VISIBILITY:
                    $visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
                    break;
                case Ids::PRIVATE_VISIBILITY:
                    $visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
                    break;
                case Ids::INVITATION_VISIBILITY:
                    $visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
                    break;
                default:
                    $visibility = "Unknow";
                    break;
            }
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
                $visibility
            ));        
            $ev->call();
        }
        $User = MainAPI::getUser($player->getName());
        if ($User instanceof UserEntity && $User->faction !== null && $User->rank !== null) {
            switch ($User->rank) {
                case Ids::RECRUIT_ID:
                    $rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
                    break;
                case Ids::MEMBER_ID:
                    $rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
                    break;
                case Ids::COOWNER_ID:
                    $rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
                    break;
                case Ids::OWNER_ID:
                    $rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
                    break;
                default: 
                    $rank = "Unknow";
                    break;
            }
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                $rank
            ));        
            $ev->call();
        } else {
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                Utils::getText($player->getName(), "NO_FACTION_TAG")
            ));        
            $ev->call();
        }
    }

    public function onPropertyTransfer(FactionPropertyTransferEvent $event): void {
        $originUser = MainAPI::getUser($event->getPlayer()->getName());
        switch ($originUser->rank) {
            case Ids::RECRUIT_ID:
                $rank = Utils::getText($event->getPlayer()->getName(), "RECRUIT_RANK_NAME");
                break;
            case Ids::MEMBER_ID:
                $rank = Utils::getText($event->getPlayer()->getName(), "MEMBER_RANK_NAME");
                break;
            case Ids::COOWNER_ID:
                $rank = Utils::getText($event->getPlayer()->getName(), "COOWNER_RANK_NAME");
                break;
            case Ids::OWNER_ID:
                $rank = Utils::getText($event->getPlayer()->getName(), "OWNER_RANK_NAME");
                break;
            default: 
                $rank = "Unknow";
                break;
        }
        $ev = new PlayerTagUpdateEvent($event->getPlayer(), new ScoreTag(
            Ids::HUD_FACTIONMASTER_PLAYER_RANK,
            $rank
        ));        
        $ev->call();
        $targetPlayer = Main::getInstance()->getServer()->getPlayer($event->getTarget()->name);
        if (!$targetPlayer instanceof Player) return;
        switch ($event->getTarget()->rank) {
            case Ids::RECRUIT_ID:
                $rank = Utils::getText($targetPlayer->getName(), "RECRUIT_RANK_NAME");
                break;
            case Ids::MEMBER_ID:
                $rank = Utils::getText($targetPlayer->getName(), "MEMBER_RANK_NAME");
                break;
            case Ids::COOWNER_ID:
                $rank = Utils::getText($targetPlayer->getName(), "COOWNER_RANK_NAME");
                break;
            case Ids::OWNER_ID:
                $rank = Utils::getText($event->getPlayer()->getName(), "OWNER_RANK_NAME");
                break;
            default: 
                $rank = "Unknow";
                break;
        }
        $ev = new PlayerTagUpdateEvent($targetPlayer, new ScoreTag(
            Ids::HUD_FACTIONMASTER_PLAYER_RANK,
            $rank
        ));        
        $ev->call();
    }

    public function onFactionJoin(FactionJoinEvent $event): void {
        $player = $event->getPlayer();
        if (!$player instanceof Player) {
            $player =  Main::getInstance()->getServer()->getPlayer($player);
        }
        if (!$player instanceof Player) return;
        $Faction = $event->getFaction();
        if ($Faction instanceof FactionEntity) {
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_NAME,
                $Faction->name
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_POWER,
                $Faction->power
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
                $Faction->level
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_XP,
                $Faction->xp
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
                $Faction->messageFaction ?? ""
            ));
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
                $Faction->description ?? ""
            ));
            switch ($Faction->visibility) {
                case Ids::PUBLIC_VISIBILITY:
                    $visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
                    break;
                case Ids::PRIVATE_VISIBILITY:
                    $visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
                    break;
                case Ids::INVITATION_VISIBILITY:
                    $visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
                    break;
                default:
                    $visibility = "Unknow";
                    break;
            }
            $ev->call();
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
                $visibility
            ));        
            $ev->call();
        }
        $User = MainAPI::getUser($player->getName());
        if ($User instanceof UserEntity && $User->faction !== null && $User->rank !== null) {
            switch ($User->rank) {
                case Ids::RECRUIT_ID:
                    $rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
                    break;
                case Ids::MEMBER_ID:
                    $rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
                    break;
                case Ids::COOWNER_ID:
                    $rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
                    break;
                case Ids::OWNER_ID:
                    $rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
                    break;
                default: 
                    $rank = "Unknow";
                    break;
            }
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                $rank
            ));        
            $ev->call();
        } else {
            $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                Utils::getText($player->getName(), "NO_FACTION_TAG")
            ));        
            $ev->call();
        }
    }

    public function onPower(FactionPowerEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_POWER,
                    $faction->power
                ));
                $ev->call();            
            }
        }
    }

    public function onLevelChange(FactionLevelChangeEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
                    $faction->level
                ));
                $ev->call();            
            }
        }
    }

    public function onXPChange(FactionXPChangeEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_XP,
                    $faction->xp
                ));
                $ev->call();            
            }
        }
    }

    public function onMessageChange(MessageChangeEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
                    $faction->messageFaction
                ));
                $ev->call();            
            }
        }
    }

    public function onDescriptionChange(DescriptionChangeEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
                    $faction->description
                ));
                $ev->call();            
            }
        }
    }

    public function onVisibilityChange(VisibilityChangeEvent $event): void {
        $faction = $event->getFaction();
        $server = Main::getInstance()->getServer();
        foreach ($faction->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                switch ($faction->visibility) {
                    case Ids::PUBLIC_VISIBILITY:
                        $visibility = "§a" . Utils::getText($player->getName(), "PUBLIC_VISIBILITY_NAME");
                        break;
                    case Ids::PRIVATE_VISIBILITY:
                        $visibility = "§4" . Utils::getText($player->getName(), "PRIVATE_VISIBILITY_NAME");
                        break;
                    case Ids::INVITATION_VISIBILITY:
                        $visibility = "§6" . Utils::getText($player->getName(), "INVITATION_VISIBILITY_NAME");
                        break;
                    default:
                        $visibility = "Unknow";
                        break;
                }
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
                    $visibility
                ));
                $ev->call();            
            }
        }
    }

    public function onRankChange(MemberChangeRankEvent $event): void {
        $user = $event->getPlayer();
        $server = Main::getInstance()->getServer();
        $player = $server->getPlayer($user->name);
        if ($player instanceof Player) {
            if ($user->rank !== null && $user->faction !== null) {
                switch ($user->rank) {
                    case Ids::RECRUIT_ID:
                        $rank = Utils::getText($player->getName(), "RECRUIT_RANK_NAME");
                        break;
                    case Ids::MEMBER_ID:
                        $rank = Utils::getText($player->getName(), "MEMBER_RANK_NAME");
                        break;
                    case Ids::COOWNER_ID:
                        $rank = Utils::getText($player->getName(), "COOWNER_RANK_NAME");
                        break;
                    case Ids::OWNER_ID:
                        $rank = Utils::getText($player->getName(), "OWNER_RANK_NAME");
                        break;
                    default: 
                        $rank = "Unknow";
                        break;
                }
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                    $rank
                ));
                $ev->call();   
            } else {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                    Utils::getText($player->getName(), "NO_FACTION_TAG")
                ));
                $ev->call();                     
            }

        }
    }

    public function onFactionLeave(FactionLeaveEvent $event): void {
        $player = $event->getPlayer();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_NAME,
            Utils::getText($player->getName(), "NO_FACTION_TAG")
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_POWER,
            0
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
            0
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_XP,
            0
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
            ""
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
            ""
        ));
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
            Utils::getText($player->getName(), "NO_FACTION_TAG")
        ));        
        $ev->call();
        $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
            Ids::HUD_FACTIONMASTER_PLAYER_RANK,
            Utils::getText($player->getName(), "NO_FACTION_TAG")
        ));        
        $ev->call();
    }

    public function onFactionDelete(FactionDeleteEvent $event): void {
        $server = Main::getInstance()->getServer();
        foreach ($event->getFaction()->members as $name => $rank) {
            $player = $server->getPlayer($name);
            if ($player instanceof Player) {
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_NAME,
                    Utils::getText($player->getName(), "NO_FACTION_TAG")
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_POWER,
                    0
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
                    0
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_XP,
                    0
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
                    ""
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
                    ""
                ));
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
                    Utils::getText($player->getName(), "NO_FACTION_TAG")
                ));        
                $ev->call();
                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                    Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                    Utils::getText($player->getName(), "NO_FACTION_TAG")
                ));        
                $ev->call();
            }
        }
    }
}