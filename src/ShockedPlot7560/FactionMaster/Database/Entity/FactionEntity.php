<?php

namespace ShockedPlot7560\FactionMaster\Database\Entity;

use ShockedPlot7560\FactionMaster\API\MainAPI;

class FactionEntity {

    /** @var int */
    public $id;
    /** @var string */
    public $name;
    /** @var array|string|null */
    public $members;
    /** @var int */
    public $visibility;
    /** @var int */
    public $xp;
    /** @var int */
    public $level;
    /** @var string|null */
    public $description;
    /** @var string|null */
    public $messageFaction;
    /** @var array|string */
    public $ally;
    /** @var int */
    public $max_player;
    /** @var int */
    public $max_ally;
    /** @var int */
    public $max_claim;
    /** @var int */
    public $max_home;
    /** @var int */
    public $power;
    /** @var int */
    public $money;
    /** @var string|array */
    public $permissions;
    /** @var string */
    public $date;

    public function __construct() {
        if (isset($this->members) && $this->members !== null && is_string($this->members)){
            $this->members = unserialize(\base64_decode($this->members));
        }
        if (isset($this->ally) && is_string($this->ally)){
            $this->ally = unserialize(\base64_decode($this->ally));
        }
        if (isset($this->permissions) && is_string($this->permissions)){
            $this->permissions = unserialize(\base64_decode($this->permissions));
        }
    }

    public function getAllyInstance() : array {
        $array = [];
        foreach ($this->ally as $key => $Ally) {
            $array[] = MainAPI::getFaction($Ally);
        }
        return $array;
    }
}