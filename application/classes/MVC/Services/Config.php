<?php

namespace MVC\Services;

use Tools\Optional;
use Tools\Singleton;

class Config {

    use Singleton;

    private $config = null;

    /**
     * @param $section
     * @param $setting
     * @return Optional
     */
    public function getSetting($section, $setting) {
        return Optional::ofNull(@$this->config[$section][$setting]);
    }

    /**
     * @param $section
     * @return Optional
     */
    public function getSection($section) {
        return Optional::ofNull(@$this->config[$section]);
    }

    function __construct() {
        $configFile = "../config.ini";
        if (file_exists($configFile)) {
            $this->config = parse_ini_file($configFile, true);
        }
    }

}
