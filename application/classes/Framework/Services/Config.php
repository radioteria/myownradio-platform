<?php

namespace Framework\Services;

use Framework\Exceptions\ApplicationException;
use Framework\Injector\Injectable;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class Config implements SingletonInterface, Injectable {

    use Singleton;

    private $config = null;

    /**
     * @param $section
     * @param $setting
     * @return Option
     */
    public function getSetting($section, $setting) {
        return Option::ofNullable(@$this->config[$section][$setting]);
    }

    /**
     * @param $section
     * @param $setting
     * @return string
     */
    public function getSettingOrFail($section, $setting) {
        return Option::ofNullable(@$this->config[$section][$setting])
            ->orThrow(ApplicationException::of("Setting \"$setting\" not found"));
    }

    /**
     * @param $section
     * @return Option
     */
    public function getSection($section) {
        return Option::ofNullable(@$this->config[$section]);
    }

    function __construct() {
        $configFile = "../config.ini";
        if (file_exists($configFile)) {
            $this->config = parse_ini_file($configFile, true);
        } else {
            error_log("Config ERROR");
        }
    }

}
