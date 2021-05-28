<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\math\Vector3;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;

class BlockBreak implements Listener {

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
                $event->getPlayer()->sendMessage(" §c>> §4You can't break on ennemie claim");
                return;
            }
        }
    }

}