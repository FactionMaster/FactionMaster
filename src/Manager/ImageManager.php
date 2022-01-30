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
use pocketmine\resourcepacks\ResourcePackException;
use pocketmine\resourcepacks\ZippedResourcePack;
use ReflectionClass;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function is_file;
use function strtolower;

class ImageManager {

	/** @var boolean */
	private static $activeImage = false;

	public static function init(Main $main) {
		if (Utils::getConfig("active-image") == true) {
			$path = Main::getInstance()->getDataFolder() . Utils::getConfig("resource-pack-path");
			if (is_file($path)) {
				try {
					$manager = $main->getServer()->getResourcePackManager();
					$ressourcePack = new ZippedResourcePack($path);
					$reflection = new ReflectionClass($manager);
					$property = $reflection->getProperty("resourcePacks");
					$property->setAccessible(true);
					$currentResourcePacks = $property->getValue($manager);
					$currentResourcePacks[] = $ressourcePack;
					$property->setValue($manager, $currentResourcePacks);
					$property = $reflection->getProperty("uuidList");
					$property->setAccessible(true);
					$currentUUIDPacks = $property->getValue($manager);
					$currentUUIDPacks[strtolower($ressourcePack->getPackId())] = $ressourcePack;
					$property->setValue($manager, $currentUUIDPacks);
					$property = $reflection->getProperty("serverForceResources");
					$property->setAccessible(true);
					$property->setValue($manager, true);
					self::setImageEnable(true);
					return;
				} catch (ResourcePackException $th) {
					$main->getLogger()->warning("An error occured in the FactionMaster load : " . $th->getMessage());
				}
			} else {
				$main->getLogger()->warning("The resource_pack given for FactionMaster image doesn't exists");
			}
			$uuid = [
				"2dea47b0-2bef-43c9-b3be-e1894a6b6b15", //official
				"dc84ba0e-f0f1-4beb-a0da-be2b1115c613" //xAliTura01
			];
			foreach ($uuid as $id) {
				if (self::isImageEnable()) {
					continue;
				}
				$pack = $main->getServer()->getResourcePackManager()->getPackById($id);
				if ($pack instanceof ResourcePack) {
					self::setImageEnable(true);
				}
			}
			if (!self::isImageEnable()) {
				$main->getLogger()->warning("To enable FactionMaster images and a better player experience, please download the dedicated FactionMaster pack. Then reactivate the images once this is done.");
			}
		} else {
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