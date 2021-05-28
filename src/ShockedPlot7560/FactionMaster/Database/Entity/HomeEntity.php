<?php

namespace ShockedPlot7560\FactionMaster\Database\Entity;

use pocketmine\math\Vector3;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class HomeEntity {

    /** @var int */
    public $id;
    /** @var string */
    public $faction;
    /** @var string */
    public $name;
    /** @var int */
    public $x;
    /** @var int */
    public $y;
    /** @var int */
    public $z;
    /** @var string */
    public $world;

    public function getToString() : string{
        return Utils::homeToString($this->x, $this->y, $this->z, $this->world);
    }

    public function getToArray() : array {
        return Utils::homeToArray($this->x, $this->y, $this->z, $this->world);
    }
    
}