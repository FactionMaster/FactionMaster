<?php

namespace ShockedPlot7560\FactionMaster\Reward;

class Reward {

    /** @var string */
    protected $nameSlug = "UNKNOW";
    /** @var string */
    protected $type = null;
    /** @var int */
    protected $value;

    /**
     * @return string
     */
    public function getName(string $playerName) : string{
        return $this->nameSlug;
    }

    /**
     * @return string
     */
    public function getType() : string {
        return $this->type;
    }

    /**
     * Récupère la quantité
     * @return int
     */
    public function getValue() : int {
        return $this->value;
    }

    /**
     * Modifie la quantité
     * @param int|string $Value Quantité à définir
     */
    public function setValue(int $value) : void{
        $this->value = $value;
    }
}