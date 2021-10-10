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

use Closure;
use pocketmine\data\bedrock\EntityLegacyIds;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityDataHelper;
use pocketmine\entity\EntityFactory;
use pocketmine\entity\Location;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\world\World;
use ShockedPlot7560\FactionMaster\Database\Table\FactionTable;
use ShockedPlot7560\FactionMaster\Entity\FactionMasterEntity;
use ShockedPlot7560\FactionMaster\Entity\ScoreboardEntity;
use pocketmine\nbt\tag\CompoundTag;
use ShockedPlot7560\FactionMaster\Main;

class LeaderboardManager {

    /** @var Main */
    private static $main;
    /** @var array */
    public static $scoreboardEntity;

    public static $queryList = [];
    public static $entityClass = [];

    public static function init(Main $main) {
        self::$main = $main;
        $factionTable = FactionTable::TABLE_NAME;
        self::$queryList["faction"] = "SELECT * FROM $factionTable ORDER BY level DESC, xp DESC, power DESC LIMIT 10";
        self::$entityClass["faction"] = ScoreboardEntity::class;
    }

    public static function placeScoreboard(string $slug, string $coordinates): void {
        if (isset(self::$queryList[$slug])) {
            EntityFactory::getInstance()->register(self::$entityClass[$slug], function(World $world, CompoundTag $nbt) use ($slug) {
                return new self::$entityClass[$slug](EntityDataHelper::parseLocation($nbt, $world), $nbt);
            }, [self::$entityClass[$slug]::getEntityName()], EntityLegacyIds::NPC);
            if ($coordinates !== false && $coordinates !== "") {
                $coordinates = explode("|", $coordinates);
                if (count($coordinates) == 4) {
                    $levelName = $coordinates[3];
                    $level = self::$main->getServer()->getWorldManager()->getWorldByName($levelName);
                    if ($level instanceof World) {
                        $level->loadChunk((float)$coordinates[0] >> 4, (float)$coordinates[2] >> 4);
                        /** @var Entity */
                        $entity = new self::$entityClass[$slug](new Location($coordinates[0], $coordinates[1], $coordinates[2], 0.0, 0.0, $level));
                        $entity->spawnToAll();
                    } else {
                        self::$main->getLogger()->notice("An unknow world was set in leaderboard.yml, can't load faction leaderboard");
                    }            
                }
            }        
        }
    }

    public static function closeLeaderboard(?string $slug = null): void {
        if ($slug === null) {
            foreach (Main::getInstance()->getServer()->getWorldManager()->getWorlds() as $level) {
                foreach ($level->getEntities() as $entity) {
                    if ($entity instanceof FactionMasterEntity) {
                        $entity->close();
                    }
                }
            }
        } else {
            foreach (Main::getInstance()->getServer()->getWorldManager()->getWorlds() as $level) {
                foreach ($level->getEntities() as $entity) {
                    if ($entity instanceof self::$entityClass[$slug]) {
                        $entity->close();
                    }
                }
            }
        }
    }
}