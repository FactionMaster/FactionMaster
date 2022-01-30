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
use ShockedPlot7560\FactionMaster\Button\Back;
use ShockedPlot7560\FactionMaster\Button\ChangePermission;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Route\RouteSlug;
use ShockedPlot7560\FactionMaster\Utils\Ids;

class ManagePermissionCollection extends Collection {
	/** @deprecated */
	const SLUG = "managePermissionCollection";

	public function __construct() {
		parent::__construct(self::MANAGE_PERMISSION_COLLECTION);
		$this->registerCallable(self::MANAGE_PERMISSION_COLLECTION, function (Player $player, UserEntity $user) {
			if ($user->getRank() > Ids::RECRUIT_ID) {
				$this->register(new ChangePermission("RECRUIT_RANK_NAME", Ids::RECRUIT_ID));
			}

			if ($user->getRank() > Ids::MEMBER_ID) {
				$this->register(new ChangePermission("MEMBER_RANK_NAME", Ids::MEMBER_ID));
			}

			if ($user->getRank() > Ids::COOWNER_ID) {
				$this->register(new ChangePermission("COOWNER_RANK_NAME", Ids::COOWNER_ID));
			}

			$this->register(new Back(RouterFactory::get(RouteSlug::MANAGE_PERMISSION_ROUTE)->getBackRoute()));
		});
	}
}