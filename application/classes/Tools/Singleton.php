<?php

namespace Tools;


trait Singleton {

    protected static $_instance = [];

    /**
     * @return static
     */
    public static function getInstance() {
        $calledClass = get_called_class();
        $calledArgs = func_get_args();
        $hash = serialize($calledArgs);

        if (!isset(self::$_instance[$hash])) {
            $reflector = new \ReflectionClass($calledClass);
            self::$_instance[$hash] = $reflector->newInstanceArgs($calledArgs);
            //$constructor->invokeArgs(self::$_instance[$hash], $calledArgs);
        }
        return self::$_instance[$hash];
    }

    /**
     * @return bool
     */
    public static function hasInstance() {
        $hash = serialize(func_get_args());
        return isset(self::$_instance[$hash]) ? true : false;
    }

    public static function killInstance() {
        $hash = serialize(func_get_args());
        unset(self::$_instance[$hash]);
    }
}
