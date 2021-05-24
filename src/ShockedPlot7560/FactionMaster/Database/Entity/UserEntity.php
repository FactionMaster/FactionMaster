<?php

namespace ShockedPlot7560\FactionMaster\Database\Entity;

class UserEntity {
    
    /** @var string */
    public $name;
    /** @var string */
    public $faction;
    /** @var int */
    public $rank;

    public function __construct(?string $name = null, ?int $rankId = null)
    {
        if ($name !== null) $this->name = $name;
        if ($rankId !== null) $this->rankId = $rankId;
    }

}