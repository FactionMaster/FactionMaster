<?php

namespace ShockedPlot7560\FactionMaster\Reward;

interface RewardInterface {

    public function __construct(int $value = 0);

    public function executeGet(string $factionName, ?int $value = null) : bool;
    
    /**
     * @return true|string Return the string slug error
     */
    public function executeCost(string $factionName, ?int $value = null);

    public function getName(string $playerName) : string;

    public function getType() : string;

    public function getValue() : int;

    public function setValue(int $value) : void;
}