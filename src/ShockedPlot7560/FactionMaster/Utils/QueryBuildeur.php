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

use function count;

class QueryBuildeur {
	const AND_MODE = 1;

	const SIMPLE_INSERT_MODE = 2;
	const PREPARE_INSERT_MODE = 3;

	const SET_MODE = 4;

	public static function buildConditions(array $conditions, int $mode = self::AND_MODE) : string {
		$conditionsString = "";
		switch ($mode) {
			case self::AND_MODE:
				foreach ($conditions as $key => $value) {
					if (count($conditions) > 1) {
						$conditionsString .= "$key = :$key AND ";
						unset($conditions[$key]);
					} else {
						$conditionsString .= "$key = :$key";
					}
				}
				break;
		}
		return $conditionsString;
	}

	public static function buildInsert(array $data, int $mode = self::SIMPLE_INSERT_MODE) : string {
		$insertString = "";
		foreach ($data as $key => $value) {
			switch ($mode) {
				case self::SIMPLE_INSERT_MODE:
					if (count($data) > 1) {
						$patern = "$key, ";
					} else {
						$patern = "$key";
					}
					break;
				case self::PREPARE_INSERT_MODE:
					if (count($data) > 1) {
						$patern = ":$key, ";
					} else {
						$patern = ":$key";
					}
					break;
				default:
					$patern = "";
					break;
			}
			$insertString .= $patern;
			unset($data[$key]);
		}

		return $insertString;
	}

	public static function buildSet(array $data, int $mode = self::SET_MODE) : string {
		$setString = "";
		switch ($mode) {
			case self::SET_MODE:
				foreach ($data as $key => $value) {
					if (count($data) > 1) {
						$setString .= "$key = :$key, ";
						unset($data[$key]);
					} else {
						$setString .= "$key = :$key";
					}
				}
				break;
		}
		return $setString;
	}
}