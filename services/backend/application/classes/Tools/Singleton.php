<?php

namespace Tools;


use ReflectionClass;
use ReflectionException;

trait Singleton
{
    protected static array $_instance = [];

    /**
     * @return static
     * @throws ReflectionException
     */
    public static function getInstance(): Singleton
    {
        $calledClass = get_called_class();
        $calledArgs = func_get_args();
        $hash = serialize($calledArgs);

        if (!isset(self::$_instance[$hash])) {
            $reflector = new ReflectionClass($calledClass);
            self::$_instance[$hash] = $reflector->newInstanceArgs($calledArgs);
        }
        return self::$_instance[$hash];
    }

    public static function hasInstance(): bool
    {
        $hash = serialize(func_get_args());
        return isset(self::$_instance[$hash]);
    }

    public static function killInstance(): void
    {
        $hash = serialize(func_get_args());
        unset(self::$_instance[$hash]);
    }
}
