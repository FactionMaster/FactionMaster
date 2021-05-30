<?php

namespace ShockedPlot7560\FactionMaster\Event;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerLoginEvent;
use ShockedPlot7560\FactionMaster\API\MainAPI;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class PlayerLogin implements Listener {

    /** @var Main */
    private $Main;

    public function __construct(Main $Main)
    {
        $this->Main = $Main;
    }

    public function onJoin(PlayerLoginEvent $event) {
        $playerName = $event->getPlayer()->getName();
        $UserEntity = MainAPI::getUser($playerName);
        if ($UserEntity === null) {
            MainAPI::$languages[$playerName] = Utils::getConfigLang("default-language");
            if (!MainAPI::addUser($playerName)) {
                $event->setKickMessage(Utils::getText($event->getPlayer()->getName(), "ERROR_DATA_SAVING"));
                $event->setCancelled(true);
            }
        }else{
            MainAPI::$languages[$playerName] = $UserEntity->language;
        }
        return;
    }
}