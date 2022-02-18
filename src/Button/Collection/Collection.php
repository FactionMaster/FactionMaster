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

use pocketmine\player\Player;
use ShockedPlot7560\FactionMaster\Button\Button;
use ShockedPlot7560\FactionMaster\libs\Vecnavium\FormsUI\SimpleForm;
use ShockedPlot7560\FactionMaster\Manager\ImageManager;
use function array_values;
use function call_user_func_array;
use function count;

class Collection implements CollectionSlug {

	/** @var Button[] */
	protected $buttonsList;
	/** @var string */
	protected $slug;
	/** @var callable[] */
	protected $processFunction;

	public function __construct(string $slug) {
		$this->slug = $slug;
	}

	public function registerCallable(string $slug, callable $callable) {
		$this->processFunction[$slug] = $callable;
	}

	public function register(Button $button, ?int $index = null, bool $override = false): void {
		if ($index === null) {
			$this->buttonsList[] = $button;
		} else {
			if ($override) {
				$this->buttonsList[$index] = $button;
			} else {
				$newElement = $button;
				for ($i = $index; $i < count($this->buttonsList); $i++) {
					$oldElement = $this->buttonsList[$i];
					$this->buttonsList[$i] = $newElement;
					$newElement = $oldElement;
				}
				$this->buttonsList[$i + 1] = $newElement;
				$this->buttonsList = array_values($this->buttonsList);
			}
		}
	}

	public function generateButtons(SimpleForm $form, string $playerName): SimpleForm {
		foreach ($this->buttonsList as $key => $button) {
			if ($button->hasAccess($playerName)) {
				if (ImageManager::isImageEnable() === true && $button->getImgPath() !== "") {
					$form->addButton($button->getContent($playerName), $button->getImgType(), $button->getImgPath());
				} else {
					$form->addButton($button->getContent($playerName));
				}
			} else {
				unset($this->buttonsList[$key]);
			}
		}
		$this->buttonsList = array_values($this->buttonsList);
		return $form;
	}

	public function process(int $keyButtonPress, Player $player): void {
		if (isset($this->buttonsList[$keyButtonPress])) {
			$this->buttonsList[$keyButtonPress]->call($player);
		}
	}

	public function getSlug(): string {
		return $this->slug;
	}

	public function init(...$parameter): self {
		$this->buttonsList = [];
		foreach ($this->processFunction as $callable) {
			call_user_func_array($callable, $parameter);
		}
		return $this;
	}
}