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

class RouterFactory {

	/** @var array */
	private static $list;

	public static function init(): void {
		self::registerRoute(new MainRoute());
		self::registerRoute(new CreateFactionRoute());
		self::registerRoute(new ConfirmationRoute());
		self::registerRoute(new TopFactionRoute());
		self::registerRoute(new MembersViewRoute());
		self::registerRoute(new HomesViewRoute());
		self::registerRoute(new ChangeLangueRoute());
		self::registerRoute(new MembersOptionRoute());
		self::registerRoute(new MembersManageRoute());
		self::registerRoute(new ManageMemberRoute());
		self::registerRoute(new ManageMemberRankRoute());
		self::registerRoute(new MembersSendInvitationRoute());
		self::registerRoute(new MembersInvitationSendRoute());
		self::registerRoute(new ManageMemberInvitationRoute());
		self::registerRoute(new MembersRequestReceiveRoute());
		self::registerRoute(new ManageJoinRequestRoute());
		self::registerRoute(new ManagePermissionRoute());
		self::registerRoute(new PermissionChangeRoute());
		self::registerRoute(new FactionOptionRoute());
		self::registerRoute(new MessageChangeRoute());
		self::registerRoute(new DescriptionChangeRoute());
		self::registerRoute(new VisibilityChangeRoute());
		self::registerRoute(new ManageLevelRoute());
		self::registerRoute(new AllianceOptionRoute());
		self::registerRoute(new AllianceRequestReceiveRoute());
		self::registerRoute(new ManageAllianceRequestRoute());
		self::registerRoute(new ManageAllianceInvitationRoute());
		self::registerRoute(new AllianceSendInvitationRoute());
		self::registerRoute(new ManageAllianceRoute());
		self::registerRoute(new JoinFactionRoute());
		self::registerRoute(new JoinSendInvitationRoute());
		self::registerRoute(new ManageJoinInvitationRoute());
		self::registerRoute(new ManageJoinRequestRoute());
		self::registerRoute(new JoinInvitationSendRoute());
		self::registerRoute(new JoinRequestReceiveRoute());
		self::registerRoute(new ManageMemberRequestRoute());
		self::registerRoute(new AllianceInvitationSendRoute());
		self::registerRoute(new LeaderboardRoute());
		self::registerRoute(new LeaderboardShowRoute());
	}

	/**
	 * Use to register or overwrite a new route
	 * @param Route $route A class implements the Route interface
	 * @param boolean $override (Default: false) If it's set to true and the slug route are already use, it will be overwrite
	 */
	public static function registerRoute(Route $route, bool $override = false): void {
		$slug = $route->getSlug();
		if (self::isRegistered($slug) && $override === false) {
			return;
		}

		self::$list[$slug] = $route;
	}

	public static function get(string $slug): ?Route {
		return self::$list[$slug] ?? null;
	}

	public static function isRegistered(string $slug): bool {
		return isset(self::$list[$slug]);
	}

	public static function getAll(): array {
		return self::$list;
	}
}