<?php

class myredis
{

    private static $handle = NULL;

    private static function init()
    {
        self::$handle = new Redis();
        self::$handle->connect('127.0.0.1', 6379);
    }

    static function set($key, $val)
    {
        if (is_null(self::$handle))
        {
            self::init();
        }
        self::$handle->set($key, $val);
    }

    static function get($key)
    {
        if (is_null(self::$handle))
        {
            self::init();
        }
        return self::$handle->get($key);
    }
    
    static function handle()
    {
        if (is_null(self::$handle))
        {
            self::init();
        }
        return self::$handle;        
    }

}
