<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 15:36
 */

namespace Tools\Optional;


class Transform {

    /**
     * @param $name
     * @return \Closure
     */
    public static function method($name) {
        return function ($obj) use (&$name) {
            return $obj->$name();
        };
    }

    /**
     * @param $name
     * @return \Closure
     */
    public static function field($name) {
        return function ($obj) use (&$name) {
            return $obj->$name;
        };
    }

    /**
     * @param $key
     * @return \Closure
     */
    public static function key($key) {
        return function ($arr) use (&$key) {
            return $arr[$key];
        };
    }

    /**
     * @return \Closure
     */
    public static function toBoolean() {
        return function ($value) {
            return boolval($value);
        };
    }

    /**
     * @return \Closure
     */
    public static function trim() {
        return function ($value) {
            return trim($value);
        };
    }

    /**
     * @return \Closure
     */
    public static function toNumber() {
        return function ($value) {
            return intval($value);
        };
    }

    /**
     * @param mixed $class Class name or object instance
     * @param string $method Method name to invoke
     * @return \Closure
     */
    public static function call($class, $method) {
        return function ($value) use (&$class, &$method) {
            return is_string($class)
                ? $class::$method($value)
                : (new $class)->$method($value);
        };
    }

}


