<?php

/*
 *
 *      ______           __  _                __  ___           __
 *     / ____/___ ______/ /_(_)___  ____     /  |/  /___ ______/ /____  _____
 *    / /_  / __ `/ ___/ __/ / __ \/ __ \   / /|_/ / __ `/ ___/ __/ _ \/ ___/
 *   / __/ / /_/ / /__/ /_/ / /_/ / / / /  / /  / / /_/ (__  ) /_/  __/ /  
 *  /_/    \__,_/\___/\__/_/\____/_/ /_/  /_/  /_/\__,_/____/\__/\___/_/ 
 *
 * FactionMaster - A Faction plugin for PocketMine-MP
 * This file is part of FactionMaster
 * 
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 * @author ShockedPlot7560 
 * @link https://github.com/ShockedPlot7560
 * 
 *
*/

namespace ShockedPlot7560\FactionMaster\Button\Collection;

use jojoe77777\FormAPI\SimpleForm;
use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\Button;

class Collection {

    /** @var \ShockedPlot7560\FactionMaster\Button\Button[] */
    protected $ButtonsList;
    protected $slug;
    /** @var callable[] */
    protected $processFunction;

    public function __construct(string $slug) {
        $this->slug = $slug;
    }

    public function registerCallable(string $slug, callable $callable) {
        $this->processFunction[$slug] = $callable;
    }

    public function register(Button $Button, ?int $index = null, bool $override = false) {
        if ($index === null) {
            $this->ButtonsList[] = $Button;
        }else {
            if ($override) {
                $this->ButtonsList[$index] = $Button;
            }else{
                $newElement = $Button;
                for ($i=$index; $i < count($this->ButtonsList) ; $i++) { 
                    $oldElement = $this->ButtonsList[$i];
                    $this->ButtonsList[$i] = $newElement;
                    $newElement = $oldElement;
                }
                $this->ButtonsList[$i+1] = $newElement;
                $this->ButtonsList = \array_values($this->ButtonsList);
            }
        }
    }

    public function generateButtons(SimpleForm $Form, string $playerName) : SimpleForm {
        foreach ($this->ButtonsList as $key => $Button) {
            if ($Button->hasAccess($playerName)) {
                $Form->addButton($Button->getContent($playerName));
            }else{
                unset($this->ButtonsList[$key]);
            }
        }
        $this->ButtonsList = \array_values($this->ButtonsList);
        return $Form;
    }

    public function process(int $keyButtonPress, Player $Player){
        $this->ButtonsList[$keyButtonPress]->call($Player);
    }

    public function getSlug() : string {
        return $this->slug;
    }

    public function init(... $parameter) : self {
        $this->ButtonsList = [];
        foreach ($this->processFunction as $callable) {
            call_user_func_array($callable, $parameter);
        }
        return $this;
    }
}