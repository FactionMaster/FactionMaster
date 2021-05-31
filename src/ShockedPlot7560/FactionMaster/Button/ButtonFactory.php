<?php

namespace ShockedPlot7560\FactionMaster\Button;

use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionFac;
use ShockedPlot7560\FactionMaster\Button\Collection\MainCollectionNoFac;

class ButtonFactory {

    /** @var array */
    private static $list;

    public static function init() : void {
        
        self::registerCollection(new MainCollectionNoFac());
        self::registerCollection(new MainCollectionFac());

    }

    public static function registerCollection(ButtonCollection $collection, bool $override = false) : void {
        $slug = $collection->getSlug();
        if (self::isRegistered($slug) && $override === false) return;
        self::$list[$slug] = $collection;
    }

    public static function get(string $slug) : ?ButtonCollection {
        return self::$list[$slug] ?? null;
    }

    public static function isRegistered(string $slug) : bool {
        return isset(self::$list[$slug]);
    }

    public static function getAll() : array {
        return self::$list;
    }
}