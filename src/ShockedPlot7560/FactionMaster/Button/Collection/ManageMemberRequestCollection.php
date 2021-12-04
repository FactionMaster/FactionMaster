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
use ShockedPlot7560\FactionMaster\Button\AcceptMemberToFac;
use ShockedPlot7560\FactionMaster\Button\Back;
use ShockedPlot7560\FactionMaster\Button\DeleteRequest;
use ShockedPlot7560\FactionMaster\Database\Entity\InvitationEntity;
use ShockedPlot7560\FactionMaster\Database\Entity\UserEntity;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\ManageMemberRequestRoute;
use ShockedPlot7560\FactionMaster\Route\MembersRequestReceiveRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;

class ManageMemberRequestCollection extends Collection {
	const SLUG = "manageMemberRequestCollection";

	public function __construct() {
		parent::__construct(self::SLUG);
		$this->registerCallable(self::SLUG, function (Player $player, UserEntity $user, InvitationEntity $request) {
			$this->register(new AcceptMemberToFac($request));
			$this->register(new DeleteRequest(
				$request,
				MembersRequestReceiveRoute::SLUG,
				ManageMemberRequestRoute::SLUG,
				[PermissionIds::PERMISSION_REFUSE_MEMBER_DEMAND]
			));
			$this->register(new Back(RouterFactory::get(ManageMemberRequestRoute::SLUG)->getBackRoute()));
		});
	}
}