<?php

namespace ShockedPlot7560\FactionMaster\Migration;

use ShockedPlot7560\FactionMaster\Main;

class MigrationManager {

    private static $list = [];

    public static function init() {
        self::$list = [
            "2.1.2-alpha" => function () {},
            "2.1.3-alpha" => function () {}
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