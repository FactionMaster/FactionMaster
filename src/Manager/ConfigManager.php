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
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\libs\JackMD\ConfigUpdater\ConfigUpdater;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function count;
use function is_countable;
use function mkdir;

class ConfigManager {
	const CONFIG_VERSION = 15;
	const LEVEL_VERSION = 0;
	const TRANSLATION_VERSION = 3;
	const LANG_FILE_VERSION = [
		"en_EN" => 10,
		"fr_FR" => 10,
		"es_SPA" => 4,
		"tr_TR" => 1,
		"pt_BR" => 1,
		"tr_TR" => 2,
		"ru_RU" => 1
	];

	/** @var Config */
	private static $config;
	/** @var Config */
	private static $level;
	/** @var Config */
	private static $translation;
	/** @var Config */
	private static $version;
	/** @var Config[] */
	private static $lang;
	/** @var Config */
	private static $leaderboard;

	public static function init(Main $main): void {
		@mkdir(Utils::getDataFolder());
		@mkdir(Utils::getLangFile());

		$main->saveDefaultConfig();
		$main->saveResource('translation.yml');
		$main->saveResource('level.yml');
		$main->saveResource('version.yml');
		$main->saveResource('leaderboard.yml');

		$resourcePack = [
			"official",
			"xAliTura01"
		];
		@mkdir(Utils::getDataFolder() . "resource_pack/");
		foreach ($resourcePack as $packName) {
			@mkdir(Utils::getDataFolder() . "resource_pack/$packName/");
			$main->saveResource("resource_pack/$packName/FactionMaster-$packName.zip");
		}

		self::$config = Utils::getConfigFile("config");
		self::$level = Utils::getConfigFile("level");
		self::$translation = Utils::getConfigFile("translation");
		self::$version = Utils::getConfigFile("version");
		self::$leaderboard = Utils::getConfigFile("leaderboard");

		ConfigUpdater::checkUpdate($main, self::getConfig(), "file-version", self::CONFIG_VERSION);
		ConfigUpdater::checkUpdate($main, self::getLevelConfig(), "file-version", self::LEVEL_VERSION);
		ConfigUpdater::checkUpdate($main, self::getTranslationConfig(), "file-version", self::TRANSLATION_VERSION);

		if (is_countable(self::getTranslationConfig()->get("languages"))) {
			if (count(self::getTranslationConfig()->get("languages")) > 0) {
				foreach (self::getTranslationConfig()->get("languages") as $language) {
					ConfigUpdater::checkUpdate($main, Utils::getConfigLangFile($language), "file-version", self::LANG_FILE_VERSION[$language]);
					$main->saveResource("lang/$language.yml");
					self::$lang[$language] = Utils::getConfigLangFile($language);
				}
			}
		} else {
			Main::getInstance()->getLogger()->error("A corrupted file has been detected, please reload your server, the base file was updated normally");
			return;
		}
	}

	public static function getConfig(): ?Config {
		return self::$config;
	}

	public static function getVersionConfig(): ?Config {
		return self::$version;
	}

	public static function getLevelConfig(): ?Config {
		return self::$level;
	}

	public static function getTranslationConfig(): ?Config {
		return self::$translation;
	}

	public static function getLangConfig(string $lang): ?Config {
		return self::$lang[$lang] ?? null;
	}

	public static function getLeaderboardConfig(): Config {
		return self::$leaderboard;
	}

	/** @return Config[] */
	public static function getLangsConfig(): array {
		return self::$lang;
	}
}
