<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\block\Chest;
use pocketmine\block\Door;
use pocketmine\block\FenceGate;
use pocketmine\block\Furnace;
use pocketmine\block\ItemFrame;
use pocketmine\block\Trapdoor;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\item\Bucket;
use pocketmine\item\Hoe;
use pocketmine\item\Shovel;
use pocketmine\level\format\Chunk;
use pocketmine\math\Vector3;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;

class Interact implements Listener {

    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;   
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
                        $event->getPlayer()->sendMessage(" §c>> §4You can't use/interact with that in ennemie claim");
                        return;
                    }
                }
        }
    }

}