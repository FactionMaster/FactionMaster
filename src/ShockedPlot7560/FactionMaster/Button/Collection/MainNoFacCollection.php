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

use pocketmine\Player;
use ShockedPlot7560\FactionMaster\Button\ChangeLanguage;
use ShockedPlot7560\FactionMaster\Button\CreateFaction;
use ShockedPlot7560\FactionMaster\Button\FactionsTop;
use ShockedPlot7560\FactionMaster\Button\JoinFaction;
use ShockedPlot7560\FactionMaster\Button\Quit;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;

class MainNoFacCollection extends Collection {
	const SLUG = "mainNoFacCollection";

	public function __construct() {
		parent::__construct(self::SLUG);
		$this->registerCallable(self::SLUG, function (Player $player, UserEntity $user) {
			$this->register(new CreateFaction());
			$this->register(new JoinFaction());
			$this->register(new FactionsTop());
			$this->register(new ChangeLanguage());
			$this->register(new Quit());
		});
	}
}