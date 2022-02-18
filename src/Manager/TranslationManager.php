<?php

namespace ShockedPlot7560\FactionMaster\Manager;

use ShockedPlot7560\FactionMaster\FactionMaster;
use ShockedPlot7560\FactionMaster\FactionMaster as Main;
use ShockedPlot7560\FactionMaster\Utils\Utils;
use function array_keys;
use function array_merge;
use function in_array;

class TranslationManager {
	private static $translations = [];
	private static $default;
	public static function init(Main $main) {
		$languages = Utils::getConfigLang("languages");
		foreach ($languages as $slug => $fileName) {
			$config = Utils::getConfigLangFile($fileName);
			$all = $config->getAll();
			unset($all["file-version"]);
			self::registerLang($slug, $all);
		}
		if (in_array(Utils::getConfigLang("default-language"), array_keys($languages), true)) {
			self::$default = Utils::getConfigLang("default-language");
		} else {
			$main->getServer()->getLogger()->warning("The default lang slug given is not correct, changed to EN");
			self::$default = "EN";
		}
	}

	public static function registerLang(string $lang, array $content) {
		if (!isset(self::$translations[$lang])) {
			self::$translations[$lang] = [];
		}
		self::$translations[$lang] = array_merge(self::$translations[$lang], $content);
	}

	public static function getDefault(): string {
		return self::$default;
	}

	public static function getTranslation(string $slug, string $lang): string {
		if (isset(self::$translations[$lang][$slug])) {
			return self::$translations[$lang][$slug];
		} elseif (isset(self::$translations[self::$default][$slug])) {
			return self::$translations[self::$default][$slug];
		} elseif (isset(self::$translations["EN"][$slug])) {
			return self::$translations["EN"][$slug];
		} else {
			FactionMaster::getInstance()->getLogger()->error("Translation: $slug unknown, please create an issue on the FactionMaster github to resolve.");
			return $slug;
		}
	}
}