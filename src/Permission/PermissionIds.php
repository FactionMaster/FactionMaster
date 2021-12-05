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

namespace ShockedPlot7560\FactionMaster\Permission;

interface PermissionIds {
	const PERMISSION_CHANGE_MEMBER_RANK = 0;
	const PERMISSION_KICK_MEMBER = 1;
	const PERMISSION_ACCEPT_MEMBER_DEMAND = 2;
	const PERMISSION_REFUSE_MEMBER_DEMAND = 3;
	const PERMISSION_SEND_MEMBER_INVITATION = 4;
	const PERMISSION_DELETE_PENDING_MEMBER_INVITATION = 5;
	const PERMISSION_ACCEPT_ALLIANCE_DEMAND = 6;
	const PERMISSION_REFUSE_ALLIANCE_DEMAND = 7;
	const PERMISSION_SEND_ALLIANCE_INVITATION = 8;
	const PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION = 9;
	const PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS = 10;
	const PERMISSION_CHANGE_FACTION_MESSAGE = 11;
	const PERMISSION_CHANGE_FACTION_DESCRIPTION = 12;
	const PERMISSION_DELETE_FACTION_HOME = 13;
	const PERMISSION_ADD_FACTION_HOME = 14;
	const PERMISSION_TP_FACTION_HOME = 15;
	const PERMISSION_CHANGE_FACTION_VISIBILITY = 16;
	const PERMISSION_BREAK_ALLIANCE = 17;
	const PERMISSION_ADD_CLAIM = 18;
	const PERMISSION_REMOVE_CLAIM = 19;
	const PERMISSION_LEVEL_UP = 20;
}