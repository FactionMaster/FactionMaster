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
namespace ShockedPlot7560\FactionMaster\Manager;

use pocketmine\entity\Entity;
use pocketmine\level\Level;
use pocketmine\level\Position;
use ShockedPlot7560\FactionMaster\Entity\ScoreboardEntity;
use ShockedPlot7560\FactionMaster\Main;

class LeaderboardManager {

    /** @var Main */
    private static $main;
    /** @var array */
    public static $scoreboardEntity;

    public static function init(Main $main) {
        self::$main = $main;
    }

    public static function placeScoreboard(?string $coordinates = null): void {
        Entity::registerEntity(ScoreboardEntity::class, true);
        if ($coordinates === null) $coordinates = ConfigManager::getLeaderboardConfig()->get("position");
        if ($coordinates !== false && $coordinates !== "") {
            $coordinates = explode("|", $coordinates);
            if (count($coordinates) == 4) {
                $levelName = $coordinates[3];
                $level = self::$main->getServer()->getLevelByName($levelName);
                if ($level instanceof Level) {
                    $level->loadChunk((float)$coordinates[0] >> 4, (float)$coordinates[2] >> 4);
                    $nbt = Entity::createBaseNBT(new Position((float)$coordinates[0], (float)$coordinates[1], (float)$coordinates[2], $level));
                    $scoreboard = Entity::createEntity("ScoreboardEntity", $level, $nbt);
                    $scoreboard->spawnToAll();
                    self::$scoreboardEntity = [$scoreboard->getId(), $level->getName()];
                } else {
                    self::$main->getLogger()->notice("An unknow world was set in leaderboard.yml, can't load faction leaderboard");
                }            
            }
        }
    }

    public static function despawnLeaderboard(): void {
        if (isset(self::$scoreboardEntity[1])) {
            $level = self::$main->getServer()->getLevelByName(self::$scoreboardEntity[1]);
            if ($level instanceof Level) {
                $entity = $level->getEntity(self::$scoreboardEntity[0]);
                if ($entity instanceof ScoreboardEntity) {
                    $entity->flagForDespawn();
                    $entity->despawnFromAll();
                }
            }
        }
    }

    public static function checkLeaderBoard(): void {
        $coordinates = explode("|", ConfigManager::getLeaderboardConfig()->get("position"));
        if (count($coordinates) == 4) {
            foreach (self::$main->getServer()->getLevels() as $level) {
                $entities = $level->getEntities();
                foreach ($entities as $entity) {
                    if ($entity instanceof ScoreboardEntity) {
                        $entity->flagForDespawn();
                        $entity->despawnFromAll();
                    }
                }
            }
        }
    }
}