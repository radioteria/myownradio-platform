<?php

trait Singleton {
    protected static $_instance = null;
    
    final public static function getInstance() {
        $calledClass = get_called_class();
        $calledArgs  = func_get_args();
        
        if(self::$_instance === null) {
            $reflector = new ReflectionClass($calledClass);
            
            self::$_instance = call_user_func_array(array($reflector, "newInstance"), $calledArgs);
        }

        return self::$_instance;
    }
}
