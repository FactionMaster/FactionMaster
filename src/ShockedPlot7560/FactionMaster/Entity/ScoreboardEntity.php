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

namespace ShockedPlot7560\FactionMaster\Entity;

use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\level\Level;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Task\DatabaseTask;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ScoreboardEntity extends Entity {
    
    public $gravity = 0;
    public $height = 0.01;
    public $width = 0.01;

    const NETWORK_ID = EntityIds::NPC;

    public function getName(): string {
        return "ScoreboardEntity";
    }

    public function initEntity(): void {
        parent::initEntity();
        $this->setImmobile(true);
        $this->setScale(0.0000001);
        $this->setNameTagAlwaysVisible(true);
    }

    public function tryChangeMovement(): void {}

    public function onUpdate(int $currentTick): bool {
        $id = $this->getId();
        $level = $this->getLevel();
        if ($level instanceof Level) {
            $levelName = $level->getName();
            Main::getInstance()->getServer()->getAsyncPool()->submitTask(new DatabaseTask(
                Main::getTopQuery(),
                [],
                function (array $result) use ($levelName, $id) {
                    $nametag = Utils::getConfig("faction-scoreboard-header") . "\n";
                    foreach ($result as $faction) {
                        $newLine = Utils::getConfig("faction-scoreboard-lign");
                        $newLine = str_replace(["{factionName}", "{level}", "{power}"], [$faction->name, $faction->level, $faction->power], $newLine);
                        $nametag .= $newLine . "\n";
                    }
                    Main::getInstance()->getServer()->getLevelByName($levelName)->getEntity($id)->setNameTag($nametag);
                },
                FactionEntity::class
            ));        
        }
        return parent::onUpdate($currentTick);
    }

    public function attack(EntityDamageEvent $source): void {
        $source->setCancelled(true);
    }

}