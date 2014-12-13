<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace MVC;

use MVC\Exceptions\ControllerException;
use MVC\Exceptions\DocNotFoundException;
use MVC\Services\HttpGet;
use MVC\Services\HttpRequest;
use MVC\Services\HttpResponse;
use Tools\Singleton;

class Router {
    private $route;

    function __construct() {
        $httpGet = HttpGet::getInstance();
        $this->route = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")->getOrElse("index"));
    }

    public function route() {

        header("Content-Type: application/json");

        try {
            $this->findRoute();
        } catch (DocNotFoundException $exception) {
            header("HTTP/1.1 404 Not Found");
            $this->outputFailure("Requested resource not found on this server");
        } catch (ControllerException $exception) {
            $this->outputFailure($exception->getMyMessage(), $exception->getMyData());
        }

    }

    public function findRoute() {
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
        $response = HttpResponse::getInstance();
        $reflection = new \ReflectionClass($response);

        print_r($reflection);

        //$this->outputOK($response->getMessage(), $response->getData());

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

    private function outputOK($message = null, $data = null) {
        $this->shout(1, $message, $data);
    }

    private function outputFailure($message = null, $data = null) {
        $this->shout(0, $message, $data);
    }

    private function shout($code = 1, $message = null, $data = null) {
        echo [
            "status" => $code,
            "message" => $message,
            "data" => $data
        ];
    }

}