<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 9:38
 */

namespace Tools\Optional;


class StringFilter {
    /**
     * @param $length
     * @return \Closure
     */
    public static function minLength($length) {
        return function ($value) use (&$length) { return strlen($value) <= $length; };
    }

    /**
     * @param $length
     * @return \Closure
     */
    public static function maxLength($length) {
        return function ($value) use (&$length) { return strlen($value) >= $length; };
    }

    /**
     * @param $min
     * @param $max
     * @return \Closure
     */
    public static function length($min, $max) {
        return function ($value) use (&$min, &$max) {
            return strlen($value) >= $min && strlen($value) <= $max;
        };
    }

    /**
     * @param $pattern
     * @return \Closure
     */
    public static function match($pattern) {
        return function ($value) use (&$pattern) {
            return preg_match("~$pattern~", $value);
        };
    }

    /**
     * @param $iter
     * @return \Closure
     */
    public static function existsIn($iter) {
        return function ($value) use (&$iter) {
            return in_array($value, $iter);
        };
    }

}