<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.03.15
 * Time: 20:07
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Framework\Injector\Injector;
use Tools\Singleton;
use Tools\SingletonInterface;

class CurrentRoute implements Injectable, SingletonInterface {

    use Singleton;

    private $route;
    private $legacy;

    public function __construct()
    {
        $route = $_GET["route"] != '/' ? substr($_GET["route"], 1) : 'index';

        $this->legacy = preg_replace('/(\.(html|php)$)|(\/$)/', '', $route);

        error_log($this->legacy);
        $route_array = explode("/", $this->legacy);
        $count = count($route_array);
        $route_array[$count - 1] = "Do" . ucfirst($route_array[$count - 1]);
        $this->route = implode("/", $route_array);
    }

    /**
     * @return string
     */
    public function getLegacy() {
        return $this->legacy;
    }

    /**
     * @return string
     */
    public function getRoute() {
        return $this->route;
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->getLegacy();
    }
} 