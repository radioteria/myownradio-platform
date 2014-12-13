<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace MVC;

use MVC\Services\HttpGet;

class Router {
    private $route;

    function __construct() {
        $httpGet = HttpGet::getInstance();
        $this->route = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")->getOrElse("index"));
    }

    public function route() {
        echo $this->route;
    }
}