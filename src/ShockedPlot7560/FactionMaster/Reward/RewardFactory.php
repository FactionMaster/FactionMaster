<?php

namespace ShockedPlot7560\FactionMaster\Reward;

class RewardFactory {

    private static $list;

    public static function init() {

        self::registerReward(new Money());
        self::registerReward(new Power());
        self::registerReward(new MemberLimit());
        
    }

    /**
     * Use to register or overwrite a new Reward
     * @param Reward $reward A class implements the RewardInterface
     * @param boolean $override (Default: false) If it's set to true and the slug are already use, it will be overwrite
     */
    public static function registerReward(RewardInterface $reward, bool $override = false) : void {
        $type = $reward->getType();
        if (self::isRegistered($type) && $override === false) return;
        self::$list[$type] = $reward;
    }

    public static function get(string $type) : ?RewardInterface {
        return self::$list[$type] ?? null;
    }

    public static function isRegistered(string $type) : bool {
        return isset(self::$list[$type]);
    }

    public static function getAll() : array {
        return self::$list;
    }

}