<?php

namespace ShockedPlot7560\FactionMaster\Database\Entity;

use ShockedPlot7560\FactionMaster\Utils\Utils;

class ClaimEntity {

    /** @var int */
    public $id;
    /** @var string */
    public $faction;
    /** @var int */
    public $x;
    /** @var int */
    public $z;
    /** @var string */
    public $world;

    public function getToString() : string{
        return Utils::claimToString($this->x, $this->z, $this->world);
    }
    
}