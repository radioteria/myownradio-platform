<?php

trait Singleton {
    protected static $_instance = [];

    /**
     * @return static
     */
    final public static function getInstance() {
        $calledClass = get_called_class();
        $calledArgs  = func_get_args();
        $hash = serialize($calledArgs);
        if(!isset(self::$_instance[$hash])) {
            $reflector = new ReflectionClass($calledClass);
            self::$_instance[$hash] = call_user_func_array(array($reflector, "newInstance"), $calledArgs);
        }
        return self::$_instance[$hash];
    }
}
