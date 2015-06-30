<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 9:25
 */

namespace Tools\Optional;


class Collect {

    public static $toOption;

    public static function init() {
        self::$toOption = function (Option $a, Option $b) {
            return $a->orElse($b);
        };
    }

}

Collect::init();