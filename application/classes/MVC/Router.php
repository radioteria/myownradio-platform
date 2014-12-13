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

        header("Content-Type: application/json");

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
        // Create instance of desired controller
        $classInstance = call_user_func([$reflection, "newInstance"]);
        // Execute controller
        $result = call_user_func_array([$classInstance, $method], $dependencies);
        // Print out json response
        echo json_encode($result);

    }

    private function loadDependencies(array $params) {
        $dependencies = [];
        foreach ($params as $param) {
            if (is_null($param->getClass()) || !$this->isInjectable($param->getClass())) {
                throw new \Exception("Object could not be injected");
            }
            $dependencies[] =
                $this->isSingleton($param->getClass()) ?
                $param->getClass()->getMethod("getInstance")->invoke(null) :
                $param->getClass()->newInstance();
        }
        return $dependencies;
    }

    private function isSingleton(\ReflectionClass $class) {
        return $this->hasTrait($class, "Tools\\Singleton");
    }

    private function isInjectable(\ReflectionClass $class) {
        return $this->hasTrait($class, "MVC\\Services\\Injectable");
    }

    private function hasTrait(\ReflectionClass $class, $traitName) {
        foreach($class->getTraits() as $trait) {
            if ($trait->getName() === $traitName)
                return true;
        }
        return false;
    }

}