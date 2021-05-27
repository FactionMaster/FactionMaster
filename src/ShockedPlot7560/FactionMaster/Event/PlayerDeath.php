<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemIds;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Database\Entity\FactionEntity;
use ShockedPlot7560\FactionMaster\Main;

class PlayerDeath implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main) 
    {
        $this->Main = $Main;
    }

    public function onDeath(PlayerDeathEvent $event) {
        $Entity = $event->getEntity();
        $cause = $Entity->getLastDamageCause();

        if ($cause instanceof EntityDamageByEntityEvent) {
            $Damager = $cause->getDamager();
            if ($Damager instanceof Player) {
                $victimInventoryArmor = $Entity->getPlayer()->getArmorInventory();

                if ($victimInventoryArmor->getHelmet()->getId() == ItemIds::AIR
                    && $victimInventoryArmor->getChestplate()->getId() == ItemIds::AIR
                    && $victimInventoryArmor->getLeggings()->getId() == ItemIds::AIR
                    && $victimInventoryArmor->getBoots()->getId() == ItemIds::AIR) return;

                $DamagerName = $Damager->getPlayer()->getName();
                $VictimName = $Entity->getPlayer()->getName();

                $VictimFaction = MainAPI::getFactionOfPlayer($VictimName);
                $DamagerFaction = MainAPI::getFactionOfPlayer($DamagerName);
                if ($DamagerFaction instanceof FactionEntity) {
                    if ($VictimFaction instanceof FactionEntity) {
                        $PowerDamager = 5;
                        $PowerVictim = -5;
                    }else{
                        $PowerDamager = 2;
                    }
                }elseif ($VictimFaction instanceof FactionEntity) {
                    $PowerVictim = -2;
                }
                if (isset($PowerDamager) && $DamagerFaction instanceof FactionEntity) MainAPI::changePower($DamagerFaction->name, $PowerDamager);
                if (isset($PowerVictim) && $VictimFaction instanceof FactionEntity) MainAPI::changePower($VictimFaction->name, $PowerVictim);
                if ($DamagerFaction instanceof FactionEntity) MainAPI::addXP($DamagerFaction->name, 1);
            }
        }
    }
}