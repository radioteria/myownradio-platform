<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.02.15
 * Time: 22:23
 */

namespace Framework\Injector;


class Injector {

    /**
     * @param \ReflectionClass $class
     * @return mixed|object
     * @throws \Exception
     */
    public static function injectByClass($class) {
        if ($class->implementsInterface("Framework\\Services\\Injectable")) {
            throw new \Exception("Object could not be injected");
        }
        if ($class->implementsInterface("Tools\\SingletonInterface")) {
            return $class->getMethod("getInstance")->invoke(null);
        } else {
            return $class->newInstanceArgs();
        }
    }

    /**
     * @param string $name
     * @return mixed|object
     * @throws \Exception
     */
    public static function injectByName($name) {
        $class = new \ReflectionClass($name);
        return self::injectByClass($class);
    }

    /**
     * @param array $names
     * @return array
     * throws \Exception
     */
    public static function injectByNameArray(array $names) {
        $array = [];
        foreach ($names as $name) {
            $array[] = self::injectByName($name);
        }
        return $array;
    }

    /**
     * @param array $classes
     * @return array
     * throws \Exception
     */
    public static function injectByClassArray(array $classes) {
        $array = [];
        foreach ($classes as $class) {
            $array[] = self::injectByClass($class);
        }
        return $array;
    }
} 