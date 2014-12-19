<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 23:07
 */

namespace Tools;


class Lang {
    public static function isNull($mixed) {
        foreach(func_get_args() as $arg) {
            if (is_null($arg)) { return true; }
        }
        return false;
    }
} 