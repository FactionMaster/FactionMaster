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

namespace ShockedPlot7560\FactionMaster\Route;

interface RouteSlug {
	public const ALLIANCE_SEND_INVITATION_ROUTE = "allianceSendInvitationRoute";
	public const ALLIANCE_INVITATION_SEND_ROUTE = "allianceInvitationSendRoute";
	public const MEMBERS_VIEW_ROUTE = "membersViewRoute";
	public const MEMBERS_REQUEST_RECEIVE_ROUTE = "membersRequestReceiveRoute";
	public const MEMBERS_OPTION_ROUTE = "membersOptionRoute";
	public const MEMBERS_MANAGE_ROUTE = "membersManageRoute";
	public const MEMBERS_INVITATION_SEND_ROUTE = "membersInvitationSendRoute";
	public const MANAGE_PERMISSION_ROUTE = "managePermissionRoute";
	public const MANAGE_MEMBER_REQUEST_ROUTE = "manageMemberRequestRoute";
	public const MANAGE_MEMBER_INVITATION_ROUTE = "manageMemberInvitationRoute";
	public const MANAGE_MEMBER_ROUTE = "manageMemberRoute";
	public const MANAGE_JOIN_REQUEST_ROUTE = "manageJoinRequestRoute";
	public const MANAGE_JOIN_INVITATION_ROUTE = "manageJoinInvitationRoute";
	public const MANAGE_ALLIANCE_REQUEST_ROUTE = "manageAllianceRequestRoute";
	public const MANAGE_ALLIANCE_INVITATION_ROUTE = "manageAllianceInvitationRoute";
	public const MANAGE_ALLIANCE_ROUTE = "manageAllianceRoute";
	public const MAIN_ROUTE = "mainRoute";
	public const JOIN_REQUEST_RECEIVE_ROUTE = "joinRequestReceiveRoute";
	public const JOIN_INVITATION_SEND_ROUTE = "joinInvitationSendRoute";
	public const JOIN_FACTION_ROUTE = "joinFactionRoute";
	public const HOMES_VIEW_ROUTE = "homesViewRoute";
	public const FACTION_OPTION_ROUTE = "factionOptionRoute";
	public const CHANGE_LANGUE_ROUTE = "changeLangueRoute";
	public const ALLIANCE_REQUEST_RECEIVE_ROUTE = "allianceRequestReceiveRoute";
	public const ALLIANCE_OPTION_ROUTE = "allianceOptionRoute";
	public const CONFIRMATION_ROUTE = "confirmationRoute";
	public const CREATE_FACTION_ROUTE = "createFactionRoute";
	public const DESCRIPTION_CHANGE_ROUTE = "descriptionChangeRoute";
	public const JOIN_SEND_INVITATION_ROUTE = "joinSendInvitationRoute";
	public const MANAGE_LEVEL_ROUTE = "manageLevelRoute";
	public const MANAGE_MEMBER_RANK_ROUTE = "manageMemberRankRoute";
	public const MEMBERS_SEND_INVITATION_ROUTE = "membersSendInvitationRoute";
	public const MESSAGE_CHANGE_ROUTE = "messageChangeRoute";
	public const PERMISSION_CHANGE_ROUTE = "permissionChangeRoute";
	public const TOP_FACTION_ROUTE = "topFactionRoute";
	public const VISIBILITY_CHANGE_ROUTE = "visibilityChangeRoute";
	public const LEADERBOARD_SHOW_ROUTE = "leaderboardShowRoute";
	public const LEADERBOARD_ROUTE = "leaderboardRoute";
}
