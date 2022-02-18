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

namespace ShockedPlot7560\FactionMaster\Utils;

interface Ids {
	const RECRUIT_ID = 0;
	const MEMBER_ID = 1;
	const COOWNER_ID = 2;
	const OWNER_ID = 3;

	const PUBLIC_VISIBILITY = 0;
	const PRIVATE_VISIBILITY = 1;
	const INVITATION_VISIBILITY = 2;

	const HUD_FACTIONMASTER_FACTION_NAME = "factionmaster.faction.name";
	const HUD_FACTIONMASTER_FACTION_POWER = "factionmaster.faction.power";
	const HUD_FACTIONMASTER_FACTION_LEVEL = "factionmaster.faction.level";
	const HUD_FACTIONMASTER_FACTION_XP = "factionmaster.faction.xp";
	const HUD_FACTIONMASTER_FACTION_MESSAGE = "factionmaster.faction.message";
	const HUD_FACTIONMASTER_FACTION_DESCRIPTION = "factionmaster.faction.description";
	const HUD_FACTIONMASTER_FACTION_VISIBILITY = "factionmaster.faction.visibility";
	const HUD_FACTIONMASTER_PLAYER_RANK = "factionmaster.player.rank";
	const HUD_FACTIONMASTER_FACTION_MAX_PLAYER = "factionmaster.faction.max.player";
	const HUD_FACTIONMASTER_FACTION_MAX_CLAIM = "factionmaster.faction.max.claim";
	const HUD_FACTIONMASTER_FACTION_MAX_HOME = "factionmaster.faction.max.home";
	const HUD_FACTIONMASTER_FACTION_MAX_ALLY = "factionmaster.faction.max.ally";
	const HUD_FACTIONMASTER_FACTION_NUMBER_PLAYER = "factionmaster.faction.number.player";
	const HUD_FACTIONMASTER_FACTION_NUMBER_CLAIM = "factionmaster.faction.number.claim";
	const HUD_FACTIONMASTER_FACTION_NUMBER_HOME = "factionmaster.faction.number.home";
	const HUD_FACTIONMASTER_FACTION_NUMBER_ALLY = "factionmaster.faction.number.ally";

	const FLAG_WARZONE = 0;
	const FLAG_SPAWN = 1;
}