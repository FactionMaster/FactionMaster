<?php

namespace ShockedPlot7560\FactionMaster\Reward;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class Power extends Reward implements RewardInterface {

    public function __construct(int $value = 0)
    {
        $this->value = $value;
        $this->nameSlug = "REWARD_POWER_NAME";
        $this->type = RewardType::POWER;
    }

    public function executeGet(string $factionName, ?int $value = null) : bool {
        if ($value !== null) $this->setValue($value);
        return MainAPI::changePower($factionName, $this->value);
    }

    public function executeCost(string $factionName, ?int $value = null) {
        if ($value !== null) $this->setValue($value);
        $Faction = MainAPI::getFaction($factionName);
        if (($Faction->power - $this->getValue()) < 0) {
            return "NO_ENOUGH_POWER";
        }
        return ($result = MainAPI::changePower($factionName, $this->value * -1)) === false ? "ERROR" : $result;
    }

}