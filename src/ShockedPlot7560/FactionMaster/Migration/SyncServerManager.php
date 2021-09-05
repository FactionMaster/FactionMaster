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

namespace ShockedPlot7560\FactionMaster\Migration;

use Ifera\ScoreHud\event\PlayerTagUpdateEvent;
use Ifera\ScoreHud\scoreboard\ScoreTag;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\HomeEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Database\Table\HomeTable;
use ShockedPlot7560\FactionMaster\Database\Table\InvitationTable;
use ShockedPlot7560\FactionMaster\Database\Table\UserTable;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class SyncServerManager {

    /** @var array */
    private static $list;

    public static function init(): void {

        self::addItem(
            "SELECT * FROM " . FactionTable::TABLE_NAME, 
            [],
            function (array $result) {
                if (count($result) > 0) {
                    MainAPI::$factions = [];
                }

                foreach ($result as $faction) {
                    if ($faction instanceof FactionEntity) {
                        MainAPI::$factions[$faction->name] = $faction;
                        if (Main::getInstance()->getServer()->getPluginManager()->getPlugin("ScoreHud") instanceof Plugin) {
                            $server = Main::getInstance()->getServer();
                            foreach ($faction->members as $name => $rank) {
                                $player = $server->getPlayer($name);
                                if ($player instanceof Player) {
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_NAME,
                                        $faction->name
                                    ));
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_POWER,
                                        $faction->power
                                    ));
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_LEVEL,
                                        $faction->level
                                    ));
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_XP,
                                        $faction->xp
                                    ));
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_MESSAGE,
                                        $faction->messageFaction ?? ""
                                    ));
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_DESCRIPTION,
                                        $faction->description ?? ""
                                    ));
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
                                    $ev->call();
                                    $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                        Ids::HUD_FACTIONMASTER_FACTION_VISIBILITY,
                                        $visibility
                                    ));   
                                    $ev->call();
                                }
                            }                        
                        }
                    }
                }
            },
            FactionEntity::class
        );
        self::addItem(
            "SELECT * FROM " . InvitationTable::TABLE_NAME,
            [],
            function (array $result) {
                if (count($result) > 0) {
                    MainAPI::$invitation = [];
                }

                foreach ($result as $invitation) {
                    if ($invitation instanceof InvitationEntity) {
                        MainAPI::$invitation[$invitation->sender . "|" . $invitation->receiver] = $invitation;
                    }

                }
            },
            InvitationEntity::class
        );
        self::addItem(
            "SELECT * FROM " . UserTable::TABLE_NAME,
            [],
            function (array $result) {
                if (count($result) > 0) {
                    MainAPI::$users = [];
                }

                foreach ($result as $user) {
                    if ($user instanceof UserEntity) {
                        MainAPI::$users[$user->name] = $user;
                        if (Main::getInstance()->getServer()->getPluginManager()->getPlugin("ScoreHud") instanceof Plugin) {
                            $player = Main::getInstance()->getServer()->getPlayer($user->name);
                            if ($player instanceof Player) {
                                switch ($user->rank) {
                                    case Ids::RECRUIT_ID:
                                        $rank = Utils::getText($user->name, "RECRUIT_RANK_NAME");
                                        break;
                                    case Ids::MEMBER_ID:
                                        $rank = Utils::getText($user->name, "MEMBER_RANK_NAME");
                                        break;
                                    case Ids::COOWNER_ID:
                                        $rank = Utils::getText($user->name, "COOWNER_RANK_NAME");
                                        break;
                                    case Ids::OWNER_ID:
                                        $rank = Utils::getText($user->name, "OWNER_RANK_NAME");
                                        break;
                                }
                                $ev = new PlayerTagUpdateEvent($player, new ScoreTag(
                                    Ids::HUD_FACTIONMASTER_PLAYER_RANK,
                                    $rank
                                ));                         
                                $ev->call();
                            }
                        }
                    }
                }
            },
            UserEntity::class
        );
        self::addItem(
            "SELECT * FROM " . HomeTable::TABLE_NAME,
            [],
            function (array $result) {
                if (count($result) > 0) {
                    MainAPI::$home = [];
                }

                foreach ($result as $home) {
                    if ($home instanceof HomeEntity) {
                        MainAPI::$home[$home->faction][$home->name] = $home;
                    }

                }
            },
            HomeEntity::class
        );

    }

    public static function addItem(string $query, array $args, callable $success, string $entity): void {
        self::$list[] = [
            $query, 
            $args, 
            $success, 
            $entity
        ];
    }

    /** @return DatabaseTask[] */
    public static function getAll(): array {
        return self::$list;
    }
}