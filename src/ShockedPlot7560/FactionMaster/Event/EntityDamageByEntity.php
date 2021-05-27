<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;

class EntityDamageByEntity implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;
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
}