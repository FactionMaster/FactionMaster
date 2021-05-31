<?php

namespace ShockedPlot7560\FactionMaster\Button;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;

class ButtonCollection {

    /** @var \ShockedPlot7560\FactionMaster\Button\Button[] */
    protected $ButtonsList;
    protected $slug;

    public function __construct(string $slug) {
        $this->slug = $slug;
    }

    public function register(Button $Button, ?int $index = null) {
        if ($index === null) {
            $this->ButtonsList[] = $Button;
        }else $this->ButtonsList[$index] = $Button;
    }

    public function generateButtons(SimpleForm $Form, string $playerName) : SimpleForm {
        foreach ($this->ButtonsList as $Button) {
            if ($Button->canAccess($playerName)) {
                $Form->addButton($Button->getContent($playerName));
            }
        }
        return $Form;
    }

    public function process(int $keyButtonPress, Player $Player){
        $this->ButtonsList[$keyButtonPress]->call($Player);
    }

    public function getSlug() : string {
        return $this->slug;
    }
}