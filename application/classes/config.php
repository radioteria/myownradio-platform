<?php

class config
{

    private static $conf = null;

    static function getSetting($section, $setting)
    {
        if (empty(self::$conf))
            self::load();

        if (empty(self::$conf[$section][$setting]))
            return null;

        return self::$conf[$section][$setting];
    }

    static function getSection($section)
    {
        if (empty(self::$conf))
            self::load();

        if (empty(self::$conf[$section]))
            return null;

        return self::$conf[$section];
    }

    static function load()
    {
        $conf_file = "../config.ini";
        if (file_exists($conf_file))
        {
            self::$conf = parse_ini_file($conf_file, true);
        }
    }

}
