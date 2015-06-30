<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 00:17
 */

namespace Tools\Optional;


class Filter {

    /**
     * @param \Closure ...$filters
     * @return \Closure
     */
    public static function matchAll(\Closure ...$filters) {
        return function ($obj) use (&$filters) {
            foreach ($filters as &$filter) {
                if (! $filter($obj)) {
                    return false;
                }
            }
            return true;
        };
    }

    /**
     * @param \Closure ...$filters
     * @return \Closure
     */
    public static function matchAny(\Closure ...$filters) {
        return function ($obj) use (&$filters) {
            foreach ($filters as &$filter) {
                if ($filter($obj)) {
                    return true;
                }
            }
            return false;
        };
    }

    /**
     * @return \Closure
     */
    public static function isNumber() {
        return function ($value) { return is_numeric($value); };
    }

    /**
     * @return \Closure
     */
    public static function isValidId() {
        return function ($value) { return is_numeric($value) && $value > 0; };
    }

    /**
     * @return \Closure
     */
    public static function isArray() {
        return function ($value) { return is_array($value); };
    }

    /**
     * @return \Closure
     */
    public static function notEmpty() {
        return function ($value) {
            if (is_array($value) && count($value) == 0) {
                return false;
            } else if (is_string($value) && strlen($value) == 0) {
                return false;
            } else if (is_null($value)) {
                return false;
            }
            return true;
        };
    }

    /**
     * @param $that
     * @return \Closure
     */
    public static function value($that) {
        return function ($value) use (&$that) {
            return $value === $that;
        };
    }

}

