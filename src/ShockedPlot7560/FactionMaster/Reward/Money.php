<?php

namespace ShockedPlot7560\FactionMaster\Reward;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class Money extends Reward implements RewardInterface {

    public function __construct(int $value = 0)
    {
        $this->value = $value;
        $this->nameSlug = "REWARD_MONEY_NAME";
        $this->type = RewardType::MONEY;
    }

    public function executeGet(string $factionName, ?int $value = null) : bool {
        if ($value !== null) $this->setValue($value);
        return MainAPI::updateMoney($factionName, $this->value);
    }

    public function executeCost(string $factionName, ?int $value = null) {
        if ($value !== null) $this->setValue($value);
        $Faction = MainAPI::getFaction($factionName);
        var_dump($Faction->money);
        var_dump(($Faction->money - $this->getValue()));
        if (($Faction->money - $this->getValue()) < 0) {
            return "NO_ENOUGH_MONEY";
        }
        return ($result = MainAPI::updateMoney($factionName, $this->value * -1)) === false ? "ERROR" : $result;
    }

}