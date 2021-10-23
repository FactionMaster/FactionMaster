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

namespace ShockedPlot7560\FactionMaster\Button;

use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Route\MembersOptionRoute;
use ShockedPlot7560\FactionMaster\Route\RouterFactory;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ManageMembers extends Button {
	const SLUG = "manageMembers";

	public function __construct() {
		$this->setSlug(self::SLUG)
			->setContent(function ($player) {
				return Utils::getText($player, "BUTTON_MANAGE_MEMBERS");
			})
			->setCallable(function ($player) {
				Utils::processMenu(RouterFactory::get(MembersOptionRoute::SLUG), $player);
			})
			->setPermissions([
				PermissionIds::PERMISSION_ACCEPT_MEMBER_DEMAND,
				PermissionIds::PERMISSION_REFUSE_MEMBER_DEMAND,
				PermissionIds::PERMISSION_DELETE_PENDING_MEMBER_INVITATION,
				PermissionIds::PERMISSION_KICK_MEMBER,
				PermissionIds::PERMISSION_CHANGE_MEMBER_RANK,
				PermissionIds::PERMISSION_SEND_MEMBER_INVITATION
			])
			->setImgPack("textures/img/option_member");
	}
}