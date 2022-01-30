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

interface ButtonSlug {
	public const ACCEPT_ALLY = "acceptAlly";
	public const FACTION_MEMBERS = "factionMembers";
	public const FACTION_HOME = "factionHome";
	public const TRANSFER_PROPERTY = "transferProperty";
	public const SEND_INVITATION = "sendInvitation";
	public const REQUEST_PENDING = "requestPending";
	public const REQUEST_ITEM = "requestItem";
	public const QUIT = "quit";
	public const MEMBER = "member";
	public const MANAGE_MEMBERS_MAIN = "manageMembersMain";
	public const MANAGE_MEMBERS = "manageMembers";
	public const MANAGE_MEMBER = "member";
	public const MANAGE_FACTION = "manageFaction";
	public const MANAGE_ALLIANCE = "manageAlliance";
	public const LEVEL_UP = "levelUp";
	public const LEAVE_DELETE = "leavingButton";
	public const KICK_OUT = "kickOut";
	public const JOIN_FACTION = "joinFaction";
	public const INVITATION_PENDING = "invitationPending";
	public const INVITATION_ITEM = "invitationItem";
	public const HOME = "home";
	public const DELETE_REQUEST = "deleteRequest";
	public const DELETE_INVITATION = "deleteInvitation";
	public const CREATE_FACTION = "createFaction";
	public const CHANGE_VISIBILITY = "changeVisibility";
	public const CHANGE_RANK = "changeRank";
	public const CHANGE_PERMISSIONS_MAIN = "changePermissionMain";
	public const CHANGE_PERMISSION = "changePermission";
	public const CHANGE_MESSAGE = "changeMessage";
	public const CHANGE_LANGUAGE = "changeLanguage";
	public const CHANGE_DESCRIPTION = "changeDescription";
	public const BREAK_ALLY = "breakAlly";
	public const BACK = "back";
	public const ALLY = "ally";
	public const ACCEPT_MEMBER_TO_FAC_REQUEST = "acceptMemberRequest";
	public const ACCEPT_MEMBER_REQUEST = "acceptRequest";
	public const LEADERBOARD_ITEM = "leaderboardItem";
	public const LEADERBOARD = "leaderboard";
}