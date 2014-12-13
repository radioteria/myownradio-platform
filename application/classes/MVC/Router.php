<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace MVC;

use MVC\Services\HttpGet;
use MVC\Services\HttpRequest;

class Router {
    private $route;

    function __construct() {
        $httpGet = HttpGet::getInstance();
        $this->route = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")->getOrElse("index"));
    }

    public function route() {

        $request = HttpRequest::getInstance();
        $class = str_replace("/", "\\", CONTROLLERS_ROOT . $this->route);
        $method = "do" . ucfirst($request->getMethod());

        // Reflect controller class
        loadClass($class);
        $reflection = new \ReflectionClass($class);

        // Try to find required method
        $method = $reflection->getMethod($method);
        $params = $method->getParameters();

        foreach($params as $param) {
            print_r($param->getClass()->name());
        }


        //$instance = $reflection->getMe
    }

}