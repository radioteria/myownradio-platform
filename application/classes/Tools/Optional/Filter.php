<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 00:17
 */

namespace Tools\Optional;


class Filter {

    public static $isNumber;
    public static $isValidId;
    public static $isArray;
    public static $notEmpty;

    public static function init() {
        self::$isNumber = function ($v) {
            return is_numeric($v);
        };
        self::$isValidId = function ($v) {
            return is_numeric($v) && $v > 0;
        };
        self::$isArray = function ($v) {
            return is_array($v);
        };
        self::$notEmpty = function ($v) {
            if (is_array($v) && count($v) == 0) {
                return false;
            } else if (is_string($v) && strlen($v) == 0) {
                return false;
            } else if (is_null($v)) {
                return false;
            }
            return true;
        };
    }

}

Filter::init();
