<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 12:49
 */

namespace Framework;


use ReflectionClass;

trait Object {
    /**
     * @return string
     */
    public static function className() {
        return get_called_class();
    }

    /**
     * @return ReflectionClass
     */
    public static function getClass() {
        return new ReflectionClass(self::className());
    }
} 