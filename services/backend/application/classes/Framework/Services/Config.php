<?php

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Config implements SingletonInterface, Injectable
{

    use Singleton;

    private $config = null;

    /**
     * @param $section
     * @param $setting
     * @return Optional
     */
    public function getSetting($section, $setting)
    {
        return Optional::ofNullable(@$this->config[$section][$setting]);
    }

    /**
     * @param $section
     * @return Optional
     */
    public function getSection($section)
    {
        return Optional::ofNullable(@$this->config[$section]);
    }

    function __construct()
    {
        $configFile = "../config.ini";
        if (file_exists($configFile)) {
            //error_log("Config OK");
            $this->config = parse_ini_file($configFile, true);
        } else {
            throw new \Exception("Do old config file");
        }
    }
}
