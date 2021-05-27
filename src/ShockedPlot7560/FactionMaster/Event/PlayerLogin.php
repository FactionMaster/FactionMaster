<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;

class PlayerLogin implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;
    }

    public function onJoin(PlayerLoginEvent $event) {
        $playerName = $event->getPlayer()->getName();
        if (!MainAPI::userIsRegister($playerName)) {
            if (!MainAPI::addUser($playerName)) {
                $event->setKickMessage("§6An error was occured in your data saving\nPlease contact an administrator");
                $event->setCancelled(true);
            }
        }
        return;
    }
}