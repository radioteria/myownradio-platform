<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 12:49
 */

namespace Framework;


trait Object {
    /**
     * @return string
     */
    public static function className() {
        return get_called_class();
    }

    /**
     * @return \ReflectionClass
     */
    public static function getClass() {
        return new \ReflectionClass(self::className());
    }

    /**
     * @param string $name
     * @return \ReflectionMethod
     */
    public static function getMethod($name) {
        $reflection = new \ReflectionClass(self::className());
        return $reflection->getMethod($name);
    }

    /**
     * @param string $className
     * @return $className
     */
    public function wrap($className) {
        return new $className($this);
    }
} 