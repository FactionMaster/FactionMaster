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

class CollectionFactory {

	/** @var array */
	private static $list;

	public static function init(): void {
		self::register(new ManageAllianceInvitationCollection());
		self::register(new AllianceInvitationSendCollection());
		self::register(new ManageAllianceRequestCollection());
		self::register(new AllianceRequestReceiveCollection());
		self::register(new JoinFactionCollection());
		self::register(new ManageJoinInvitationCollection());
		self::register(new JoinInvitationSendCollection());
		self::register(new ManageJoinRequestCollection());
		self::register(new JoinRequestReceiveCollection());
		self::register(new ChangeLangueCollection());
		self::register(new MainFacCollection());
		self::register(new MainNoFacCollection());
		self::register(new ManageAllianceCollection());
		self::register(new AllianceOptionCollection());
		self::register(new FactionOptionCollection());
		self::register(new ManageMemberInvitationCollection());
		self::register(new MembersInvitationSendCollection());
		self::register(new ManageMemberCollection());
		self::register(new MembersManageCollection());
		self::register(new MembersOptionCollection());
		self::register(new ManageMemberRequestCollection());
		self::register(new MembersRequestReceiveCollection());
		self::register(new ManagePermissionCollection());
		self::register(new HomesViewCollection());
		self::register(new MembersViewCollection());
		self::register(new LeaderboardCollection());
		self::register(new LeaderboardShowCollection());
	}

	public static function register(Collection $collection, bool $override = false): void {
		$slug = $collection->getSlug();
		if (self::isRegistered($slug) && $override === false) {
			return;
		}

		self::$list[$slug] = $collection;
	}

	public static function get(string $slug): ?Collection {
		return self::$list[$slug] ?? null;
	}

	public static function isRegistered(string $slug): bool {
		return isset(self::$list[$slug]);
	}

	public static function getAll(): array {
		return self::$list;
	}
}