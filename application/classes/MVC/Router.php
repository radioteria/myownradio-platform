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
use Tools\Singleton;

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
        $params = $reflection->getMethod($method)->getParameters();

        // Inject dependencies
        $dependencies = $this->loadDependencies($params);

        $classInstance = call_user_func([$reflection, "newInstance"]);
        $result = call_user_func_array([$classInstance, $method], $dependencies);

        echo json_encode($result);

    }

    private function loadDependencies(array $params) {
        $dependencies = [];
        foreach ($params as $param) {
            /** @var ReflectionParameter $param */
            $dependencies[] = call_user_func_array([$param->getClass(), "newInstance"], []);
        }
        return $dependencies;
    }

    private function isSingleton(\ReflectionClass $class) {
        $traits = $class->getTraits();
        foreach($traits as $trait) {
            if ($trait->getShortName() === "Singleton") return true;
        }
        return false;
    }

}