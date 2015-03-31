<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 02.02.15
 * Time: 22:43
 */

namespace Framework\Services;


class Invoker {
    public static function invoke($callable) {
        $function = new \ReflectionFunction($callable);
        return $function->invokeArgs(self::createObjects($function->getParameters()));
    }

    public static function invokeMethod($object, \ReflectionMethod $method) {
        $method->setAccessible(true);
        return $method->invokeArgs($object, self::createObjects($method->getParameters()));
    }

    public static function createObjects($names) {
        $args = [];

        foreach ($names as $param) {

            /** @var \ReflectionParameter $param */
            if (!$param->getClass()->implementsInterface("Framework\\Services\\Injectable")) {
                throw new \Exception("Object could not be injected");
            }

            if ($param->getClass()->implementsInterface("Tools\\SingletonInterface")) {
                $args[] = $param->getClass()->getMethod("getInstance")->invoke(null);
            } else {
                $args[] = $param->getClass()->newInstanceArgs();
            }

        }

        return $args;
    }
}