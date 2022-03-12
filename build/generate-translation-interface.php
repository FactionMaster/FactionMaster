<?php

use pocketmine\utils\Config;

require "vendor/autoload.php";

$interfacePath = dirname(__DIR__) . "/src/Utils/TranslationSlug.php";

$baseFile = "<?php

namespace ShockedPlot7560\FactionMaster\Utils;

/**
 * This document is automatically generated, do not modify it manually.
 * @see build/generate-translation-interface.php
 */
interface TranslationSlug {
%variables%
}";
$lines = [];

$enFile = dirname(__DIR__) . "/resources/lang/en_EN.yml";
$enFileStream = fopen($enFile, 'r');
$config = new Config($enFile);
foreach ($config->getAll() as $key => $trans) {
	if ($key === "file-version") {
		echo "skipped: $key\n";
		continue;
	}
	if (!isset($lines[strtoupper($key)])) {
		$lines[strtoupper($key)] = "    public const " . strtoupper($key) . " = \"" . $key . "\";";
		echo "add: $key\n";
	} else {
		echo "WARNING: duplicated key found: $key\n";
	}
}
$finalFile = str_replace("%variables%", implode("\n", $lines), $baseFile);
file_put_contents($interfacePath, $finalFile);