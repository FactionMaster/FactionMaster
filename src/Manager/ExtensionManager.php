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

use pocketmine\utils\Config;
use ShockedPlot7560\FactionMaster\Extension\Extension;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;

class ExtensionManager {

	/** @var Extension[] */
	private static $extensions = [];

	public static function registerExtension(Extension $extension): void {
		self::$extensions[$extension->getExtensionName()] = $extension;
	}

	public static function disableExtension(string $name): void {
		if (isset(self::$extensions[$name])) {
			unset(self::$extensions[$name]);
		}
	}

	public static function load(): void {
		foreach (self::$extensions as $extension) {
			$extension->execute();
		}
		self::loadLangFile();
	}

	/** @return Extension[] */
	public static function getExtensions(): array {
		return self::$extensions;
	}

	public static function loadLangFile(): void {
		$langConfigExtension = [];
		foreach (self::getExtensions() as $extension) {
			try {
				$langConfigExtension[$extension->getExtensionName()] = $extension->getLangConfig();
			} catch (\Throwable $th) {
				Main::getInstance()->getLogger()->error("Can not load the translate files of : " . $extension->getExtensionName() . ", check the return value of the function getLangConfig() and verify its key and value. If you are not the author of this extension, please inform him");
			}
		}
		foreach ($langConfigExtension as $extensionName => $langConfig) {
			foreach ($langConfig as $langSlug => $langConfigFile) {
				if (!$langConfigFile instanceof Config) {
					Main::getInstance()->getLogger()->error("Can not load the translate files of : $extensionName, check the return value of the function getLangConfig() and verify its key and value. If you are not the author of this extension, please inform him");
				} else {
					$all = $langConfigFile->getAll();
					unset($all["file-version"]);
					TranslationManager::registerLang($langSlug, $all);
				}
			}
		}
	}
}
