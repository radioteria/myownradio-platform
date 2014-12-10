<?php

class session
{

    static function get($arg)
    {
        if (!empty(filter_input(INPUT_COOKIE, 'PHPSESSID')))
        {
            if (session_status() == PHP_SESSION_NONE)
            {
                self::init();
            }
            if (isset($_SESSION[$arg]))
            {
                return $_SESSION[$arg];
            }
            else
            {
                return null;
            }
        }
        else
        {
            return null;
        }
    }
    
    static function setUnlimited()
    {
        
    }

    static function getID()
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            self::init();
        }
        return session_id();
    }
    
    static function set($arg, $val)
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            self::init();
        }
        misc::writeDebug("Write to session: {$arg} => {$val}");
        $_SESSION[$arg] = $val;
    }

    static function remove($arg)
    {
        if (session_status() == PHP_SESSION_NONE)
        {
            self::init();
        }
        if (isset($_SESSION[$arg]))
        {
            unset($_SESSION[$arg]);
        }
    }

    static function destroy()
    {
        unset($_SESSION);
        session_unset();
        session_destroy();
    }

    static function end()
    {
        if (session_status() != PHP_SESSION_NONE)
        {
            session_write_close();
            $_SESSION = null;
        }
    }

    static function init($unlimited = false)
    {
        if ($unlimited)
        {
            session_set_cookie_params(60 * 60 * 24 * 14);
        }
        session_start();
    }

}
