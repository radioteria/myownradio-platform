<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.02.15
 * Time: 22:23
 */

namespace Framework;


class Injector {
    public static function injectByName($name) {
        $reflection = new \ReflectionClass($name);
        if ($reflection->implementsInterface("Framework\\Services\\Injectable")) {
            throw new \Exception("Object could not be injected");
        }
    }
    public static function injectByClass($class) {

    }
    public static function injectByNameArray(array $names) {

    }
    public static function injectByClassArray(array $classes) {

    }
} 