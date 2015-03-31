<?php

namespace Tools;

class System {

    private static $savedTime = null;

    public static function time() {
        if (self::$savedTime === null) {
            self::$savedTime = intval(microtime(true) * 1000);
        }
        return self::$savedTime;
    }

    public static function realTime() {
        return intval(microtime(true) * 1000);
    }
}
