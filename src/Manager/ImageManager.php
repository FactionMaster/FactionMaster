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

use pocketmine\resourcepacks\ResourcePack;
use ShockedPlot7560\FactionMaster\Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;

class ImageManager {

    /** @var Main */
    private static $main;
    /** @var boolean */
    private static $activeImage;

    public static function init(Main $main) {
        self::$main = $main;
        if (Utils::getConfig("active-image") == true) {
            $pack = $main->getServer()->getResourcePackManager()->getPackById("6682bde3-ece8-4f22-8d6b-d521efc9325d");
            if (!$pack instanceof ResourcePack) {
                $main->getLogger()->warning("To enable FactionMaster images and a better player experience, please download the dedicated FactionMaster pack. Then reactivate the images once this is done.");
                self::setImageEnable(false);
            }else{
                self::setImageEnable(true);
            }
        }else{
            self::setImageEnable(false);
        }
    }

    public static function isImageEnable(): bool {
        return self::$activeImage;
    }

    public static function setImageEnable(bool $status): void {
        self::$activeImage = $status;
    }

}