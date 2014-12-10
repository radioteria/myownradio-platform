<?php

class System
{
    private static $savedTime = null;
    public static function time()
    {
        if (self::$savedTime === null)
        {
            self::$savedTime = (int) (microtime(true) * 1000);
        }
        return self::$savedTime;
    }
    
    public function currentTime()
    {
        return (int) (microtime(true) * 1000);
    }
}
