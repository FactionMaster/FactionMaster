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

    public static function init() : void {

        self::register(new MainCollectionFac());
        self::register(new MainCollectionNoFac());
        self::register(new ViewHomesCollection());
        self::register(new LanguageCollection());

        self::register(new JoinRequestListCollection());
        self::register(new JoinInvitationListCollection());
        self::register(new JoinFactionMainCollection());
        self::register(new JoinInvitationCollection());
        self::register(new JoinRequestCollection());

        self::register(new ViewMembersCollection());
        self::register(new ManageMembersCollection());
        self::register(new ManageMemberCollection());
        self::register(new ManageMembersMainCollection());
        self::register(new MemberInvitationListCollection());
        self::register(new ManageInvitationCollection());
        self::register(new ManageRequestListCollection());
        self::register(new ManageRequestCollection());

        self::register(new PermissionMainCollection());
        self::register(new ManageFactionMainCollection());

        self::register(new AllianceInvitationCollection());
        self::register(new AllianceInvitationListCollection());
        self::register(new AllianceRequestCollection());
        self::register(new AllianceRequestListCollection());
        self::register(new ManageAllianceCollection());
        self::register(new ManageAllianceMainCollection());
    }

    public static function register(Collection $Collection, bool $override = false) : void {
        $slug = $Collection->getSlug();
        if (self::isRegistered($slug) && $override === false) return;
        self::$list[$slug] = $Collection;
    }

    public static function get(string $slug) : ?Collection {
        return self::$list[$slug] ?? null;
    }

    public static function isRegistered(string $slug) : bool {
        return isset(self::$list[$slug]);
    }

    public static function getAll() : array {
        return self::$list;
    }

}