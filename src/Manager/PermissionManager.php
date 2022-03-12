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

namespace ShockedPlot7560\FactionMaster\Manager;

use ShockedPlot7560\FactionMaster\Permission\Permission;
use ShockedPlot7560\FactionMaster\Permission\PermissionException;
use ShockedPlot7560\FactionMaster\Permission\PermissionIds;
use ShockedPlot7560\FactionMaster\Permission\PermissionInterface;
use ShockedPlot7560\FactionMaster\Utils\TranslationSlug;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class PermissionManager {
	const PERMISSION_CHANGE_MEMBER_RANK = "PERMISSION_CHANGE_MEMBER_RANK";
	const PERMISSION_KICK_MEMBER = "PERMISSION_KICK_MEMBER";
	const PERMISSION_ACCEPT_MEMBER_DEMAND = "PERMISSION_ACCEPT_MEMBER_DEMAND";
	const PERMISSION_REFUSE_MEMBER_DEMAND = "PERMISSION_REFUSE_MEMBER_DEMAND";
	const PERMISSION_SEND_MEMBER_INVITATION = "PERMISSION_SEND_MEMBER_INVITATION";
	const PERMISSION_DELETE_PENDING_MEMBER_INVITATION = "PERMISSION_DELETE_PENDING_MEMBER_INVITATION";
	const PERMISSION_ACCEPT_ALLIANCE_DEMAND = "PERMISSION_ACCEPT_ALLIANCE_DEMAND";
	const PERMISSION_REFUSE_ALLIANCE_DEMAND = "PERMISSION_REFUSE_ALLIANCE_DEMAND";
	const PERMISSION_SEND_ALLIANCE_INVITATION = "PERMISSION_SEND_ALLIANCE_INVITATION";
	const PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION = "PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION";
	const PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS = "PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS";
	const PERMISSION_CHANGE_FACTION_MESSAGE = "PERMISSION_CHANGE_FACTION_MESSAGE";
	const PERMISSION_CHANGE_FACTION_DESCRIPTION = "PERMISSION_CHANGE_FACTION_DESCRIPTION";
	const PERMISSION_CHANGE_FACTION_VISIBILITY = "PERMISSION_CHANGE_FACTION_VISIBILITY";
	const PERMISSION_ADD_CLAIM = "PERMISSION_ADD_CLAIM";
	const PERMISSION_REMOVE_CLAIM = "PERMISSION_REMOVE_CLAIM";
	const PERMISSION_ADD_FACTION_HOME = "PERMISSION_ADD_FACTION_HOME";
	const PERMISSION_DELETE_FACTION_HOME = "PERMISSION_DELETE_FACTION_HOME";
	const PERMISSION_LEVEL_UP = "PERMISSION_LEVEL_UP";

	private static $permissions = [];

	public static function init() {
		self::registerPermission(new Permission(self::PERMISSION_CHANGE_MEMBER_RANK, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_CHANGE_MEMBER_RANK);}, PermissionIds::PERMISSION_CHANGE_MEMBER_RANK), true);
		self::registerPermission(new Permission(self::PERMISSION_KICK_MEMBER, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_KICK_MEMBER);}, PermissionIds::PERMISSION_KICK_MEMBER), true);
		self::registerPermission(new Permission(self::PERMISSION_ACCEPT_MEMBER_DEMAND, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_ACCEPT_MEMBER_DEMAND);}, PermissionIds::PERMISSION_ACCEPT_MEMBER_DEMAND), true);
		self::registerPermission(new Permission(self::PERMISSION_REFUSE_MEMBER_DEMAND, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_REFUSE_MEMBER_DEMAND);}, PermissionIds::PERMISSION_REFUSE_MEMBER_DEMAND), true);
		self::registerPermission(new Permission(self::PERMISSION_SEND_MEMBER_INVITATION, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_SEND_MEMBER_INVITATION);}, PermissionIds::PERMISSION_SEND_MEMBER_INVITATION), true);
		self::registerPermission(new Permission(self::PERMISSION_DELETE_PENDING_MEMBER_INVITATION, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_DELETE_PENDING_MEMBER_INVITATION);}, PermissionIds::PERMISSION_DELETE_PENDING_MEMBER_INVITATION), true);
		self::registerPermission(new Permission(self::PERMISSION_ACCEPT_ALLIANCE_DEMAND, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_ACCEPT_ALLIANCE_DEMAND);}, PermissionIds::PERMISSION_ACCEPT_ALLIANCE_DEMAND), true);
		self::registerPermission(new Permission(self::PERMISSION_REFUSE_ALLIANCE_DEMAND, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_REFUSE_ALLIANCE_DEMAND);}, PermissionIds::PERMISSION_REFUSE_ALLIANCE_DEMAND), true);
		self::registerPermission(new Permission(self::PERMISSION_SEND_ALLIANCE_INVITATION, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_SEND_ALLIANCE_INVITATION);}, PermissionIds::PERMISSION_SEND_ALLIANCE_INVITATION), true);
		self::registerPermission(new Permission(self::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION);}, PermissionIds::PERMISSION_DELETE_PENDING_ALLIANCE_INVITATION), true);
		self::registerPermission(new Permission(self::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS);}, PermissionIds::PERMISSION_MANAGE_LOWER_RANK_PERMISSIONS), true);
		self::registerPermission(new Permission(self::PERMISSION_CHANGE_FACTION_MESSAGE, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_CHANGE_FACTION_MESSAGE);}, PermissionIds::PERMISSION_CHANGE_FACTION_MESSAGE), true);
		self::registerPermission(new Permission(self::PERMISSION_CHANGE_FACTION_DESCRIPTION, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_CHANGE_FACTION_DESCRIPTION);}, PermissionIds::PERMISSION_CHANGE_FACTION_DESCRIPTION), true);
		self::registerPermission(new Permission(self::PERMISSION_CHANGE_FACTION_VISIBILITY, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_CHANGE_FACTION_VISIBILITY);}, PermissionIds::PERMISSION_CHANGE_FACTION_VISIBILITY), true);
		self::registerPermission(new Permission(self::PERMISSION_ADD_CLAIM, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_ADD_CLAIM);}, PermissionIds::PERMISSION_ADD_CLAIM), true);
		self::registerPermission(new Permission(self::PERMISSION_REMOVE_CLAIM, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_REMOVE_CLAIM);}, PermissionIds::PERMISSION_REMOVE_CLAIM), true);
		self::registerPermission(new Permission(self::PERMISSION_ADD_FACTION_HOME, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_ADD_FACTION_HOME);}, PermissionIds::PERMISSION_ADD_FACTION_HOME), true);
		self::registerPermission(new Permission(self::PERMISSION_DELETE_FACTION_HOME, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_DELETE_FACTION_HOME);}, PermissionIds::PERMISSION_DELETE_FACTION_HOME), true);
		self::registerPermission(new Permission(self::PERMISSION_LEVEL_UP, function (string $playerName) {return Utils::getText($playerName, TranslationSlug::PERMISSION_LEVEL_UP);}, PermissionIds::PERMISSION_LEVEL_UP), true);
	}

	public static function registerPermission(PermissionInterface $permission, bool $override = false): void {
		if (self::isRegister($permission->getId()) && !$override) {
			throw new PermissionException("Permission id already use, conflicts detected");
		}

		self::$permissions[$permission->getId()] = $permission;
	}

	public static function removePermission(int $id): void {
		if (isset(self::$permissions[$id])) {
			unset(self::$permissions[$id]);
		}
	}

	public static function isRegister(int $id): bool {
		return isset(self::$permissions[$id]);
	}

	/**
	 * @return Permission[]
	 */
	public static function getAll(): array {
		return self::$permissions;
	}
}