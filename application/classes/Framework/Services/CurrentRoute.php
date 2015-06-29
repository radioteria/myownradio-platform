<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.03.15
 * Time: 20:07
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class CurrentRoute implements Injectable, SingletonInterface {

    use Singleton;

    private $route, $legacy;

    function __construct() {

        $httpGet = Http\HttpGet::getInstance();

        $this->legacy = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->get("route")->getOrElse("index"));
        $route_array = explode("/", $this->legacy);
        $count = count($route_array);
        $route_array[$count - 1] = "Do" . ucfirst($route_array[$count - 1]);
        $this->route = str_replace("/", "\\", CONTROLLERS_ROOT . implode("/", $route_array));

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