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

namespace ShockedPlot7560\FactionMaster\Migration;

use ShockedPlot7560\FactionMaster\Main;

class MigrationManager {

    private static $list = [];

    public static function init() {
        self::$list = [
            "2.1.2-alpha" => function () {},
            "2.1.3-alpha" => function () {},
        ];
    }

    public static function migrate(string $version) {
        $actualVersion = "";
        foreach (self::$list as $versionName => $callable) {
            if (version_compare($version, $versionName, "<")) {
                $actualVersion = $versionName;
                Main::getInstance()->getLogger()->debug("Starting migration from $versionName");
                call_user_func($callable);
                Main::getInstance()->getLogger()->debug("Migration from $versionName finish");
            }
        }
        Main::getInstance()->version->set("migrate-version", $actualVersion);
        Main::getInstance()->version->save();
    }
}