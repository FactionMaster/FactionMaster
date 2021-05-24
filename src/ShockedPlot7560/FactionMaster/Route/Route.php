<?php

namespace ShockedPlot7560\FactionMaster\Route;

use pocketmine\Player;

interface Route {

    /**
     * Return the slug which will be use to call the action
     * @return string The slug
     */
    public function getSlug() : string;
    
    public function __invoke(Player $player, ?array $params = null);

    /**
     * Function use in the __invoke function when the panel are called
     * @return callable A function which can be used by the FormAPI method
     */
    public function call() : callable;
}