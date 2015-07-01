<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 11:32
 */

namespace Tools\Optional;


class NumberFilter {

    /**
     * "Greater than" filter
     * @param $than
     * @return \Closure
     */
    public static function gt($than) {
        return function ($value) use (&$than) { return $value > $than; };
    }

    /**
     * "Less than" filter
     * @param $than
     * @return \Closure
     */
    public static function lt($than) {
        return function ($value) use (&$than) { return $value < $than; };
    }

    /**
     * "Greater or equal" filter
     * @param $than
     * @return \Closure
     */
    public static function ge($than) {
        return function ($value) use (&$than) { return $value >= $than; };
    }

    /**
     * "Less or equal" filter
     * @param $than
     * @return \Closure
     */
    public static function le($than) {
        return function ($value) use (&$than) { return $value <= $than; };
    }

    /**
     * "Is in range" filter
     * @param $from
     * @param $to
     * @return \Closure
     */
    public static function inRange($from, $to) {
        return function ($value) use (&$from, &$to) { return $value >= $from && $value <= $to; };
    }

}