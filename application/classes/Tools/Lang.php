<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 23:07
 */

namespace Tools;


class Lang {
    /**
     * @param $mixed
     * @return bool
     */
    public static function isNull($mixed) {
        foreach (func_get_args() as $arg) {
            if (is_null($arg)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param \Generator $generator
     * @return array
     */
    public static function generatorToArray(\Generator $generator) {
        $array = [];
        foreach($generator as &$item) {
            $array[] = $item;
        }
        return $array;
    }
} 