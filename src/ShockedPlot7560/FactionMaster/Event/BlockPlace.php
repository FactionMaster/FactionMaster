<?php

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