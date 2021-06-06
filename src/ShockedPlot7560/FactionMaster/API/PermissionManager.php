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

namespace ShockedPlot7560\FactionMaster\API;

use ShockedPlot7560\FactionMaster\Utils\Ids;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class PermissionManager {

    /** @var array[] */
    private static $list;

    public static function init() : void {
        self::registerPermission("PERMISSION_CHANGE_MEMBER_RANK", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_CHANGE_MEMBER_RANK");}, Ids::PERMISSION_CHANGE_MEMBER_RANK);
        self::registerPermission("PERMISSION_KICK_MEMBER", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_KICK_MEMBER");}, Ids::PERMISSION_KICK_MEMBER);
        self::registerPermission("PERMISSION_ACCEPT_MEMBER_DEMAND", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_ACCEPT_MEMBER_DEMAND");}, Ids::PERMISSION_ACCEPT_MEMBER_DEMAND);
        self::registerPermission("PERMISSION_REFUSE_MEMBER_DEMAND", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_REFUSE_MEMBER_DEMAND");}, Ids::PERMISSION_REFUSE_MEMBER_DEMAND);
        self::registerPermission("PERMISSION_SEND_MEMBER_INVITATION", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_SEND_MEMBER_INVITATION");}, Ids::PERMISSION_SEND_MEMBER_INVITATION);
        self::registerPermission("PERMISSION_DELETE_PENDING_MEMBER_INVITATION", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_DELETE_PENDING_MEMBER_INVITATION");}, Ids::PERMISSION_DELETE_PENDING_MEMBER_INVITATION);
        self::registerPermission("PERMISSION_ACCEPT_ALLIANCE_DEMAND", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_ACCEPT_ALLIANCE_DEMAND");}, Ids::PERMISSION_ACCEPT_ALLIANCE_DEMAND);
        self::registerPermission("PERMISSION_REFUSE_ALLIANCE_DEMAND", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_REFUSE_ALLIANCE_DEMAND");}, Ids::PERMISSION_REFUSE_ALLIANCE_DEMAND);
        self::registerPermission("PERMISSION_SEND_ALLIANCE_INVITATION", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_SEND_ALLIANCE_INVITATION");}, Ids::PERMISSION_SEND_ALLIANCE_INVITATION);
        self::registerPermission("PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION");}, Ids::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION);
        self::registerPermission("PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS");}, Ids::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS);
        self::registerPermission("PERMISSION_CHANGE_FACTION_MESSAGE", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_CHANGE_FACTION_MESSAGE");}, Ids::PERMISSION_CHANGE_FACTION_MESSAGE);
        self::registerPermission("PERMISSION_CHANGE_FACTION_DESCRIPTION", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_CHANGE_FACTION_DESCRIPTION");}, Ids::PERMISSION_CHANGE_FACTION_DESCRIPTION);
        self::registerPermission("PERMISSION_CHANGE_FACTION_VISIBILITY", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_CHANGE_FACTION_VISIBILITY");}, Ids::PERMISSION_CHANGE_FACTION_VISIBILITY);
        self::registerPermission("PERMISSION_ADD_CLAIM", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_ADD_CLAIM");}, Ids::PERMISSION_ADD_CLAIM);
        self::registerPermission("PERMISSION_REMOVE_CLAIM", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_REMOVE_CLAIM");}, Ids::PERMISSION_REMOVE_CLAIM);
        self::registerPermission("PERMISSION_CHANGE_MEMBER_RANK", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_CHANGE_MEMBER_RANK");}, Ids::PERMISSION_CHANGE_MEMBER_RANK);
        self::registerPermission("PERMISSION_ADD_FACTION_HOME", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_ADD_FACTION_HOME");}, Ids::PERMISSION_ADD_FACTION_HOME);
        self::registerPermission("PERMISSION_DELETE_FACTION_HOME", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_DELETE_FACTION_HOME");}, Ids::PERMISSION_DELETE_FACTION_HOME);
        self::registerPermission("PERMISSION_LEVEL_UP", function(string $playerName) { return Utils::getText($playerName, "PERMISSION_LEVEL_UP");}, Ids::PERMISSION_LEVEL_UP);
    }

    public static function registerPermission(string $slug, callable $Name, int $ID, bool $override = false) : void {
        if (self::isRegistered($slug) && $override === false) return;
        self::$list[$slug] = [
            "slug" => $slug,
            "nameCallable" => $Name,
            "id" => $ID
        ];
    }

    public static function get(string $slug) : ?array {
        return self::$list[$slug] ?? null;
    }

    public static function isRegistered(string $slug) : bool {
        return isset(self::$list[$slug]);
    }

    public static function getAll() : array {
        return self::$list;
    }

}