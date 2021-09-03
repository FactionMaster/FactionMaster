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

        self::registerRoute(new MainPanel());
        self::registerRoute(new CreateFactionPanel());
        self::registerRoute(new ConfirmationMenu());
        self::registerRoute(new TopFactionPanel());
        self::registerRoute(new ViewFactionMembers());
        self::registerRoute(new HomeListPanel());
        self::registerRoute(new LanguagePanel());
        self::registerRoute(new ManageMainMembers());
        self::registerRoute(new ManageMembersList());
        self::registerRoute(new ManageMember());
        self::registerRoute(new MemberChangeRank());
        self::registerRoute(new NewMemberInvitation());
        self::registerRoute(new MemberInvitationList());
        self::registerRoute(new ManageMemberInvitation());
        self::registerRoute(new MemberDemandList());
        self::registerRoute(new ManageMemberDemand());
        self::registerRoute(new ChangePermissionMain());
        self::registerRoute(new RankPermissionManage());
        self::registerRoute(new ManageFactionMain());
        self::registerRoute(new ChangeMessage());
        self::registerRoute(new ChangeDescription());
        self::registerRoute(new ChangeVisibility());
        self::registerRoute(new LevelUp());
        self::registerRoute(new AllianceMainMenu());
        self::registerRoute(new AllianceInvitationList());
        self::registerRoute(new AllianceDemandList());
        self::registerRoute(new ManageAllianceDemand());
        self::registerRoute(new ManageAllianceInvitation());
        self::registerRoute(new NewAllianceInvitation());
        self::registerRoute(new ManageAlliance());
        self::registerRoute(new ManageInvitationMain());
        self::registerRoute(new NewInvitation());
        self::registerRoute(new ManageInvitation());
        self::registerRoute(new ManageDemand());
        self::registerRoute(new InvitationList());
        self::registerRoute(new DemandList());
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

    public static function getAll(): array{
        return self::$list;
    }

}