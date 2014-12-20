<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace MVC;

use Exception;
use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Exceptions\DocNotFoundException;
use MVC\Services\HttpGet;
use MVC\Services\HttpRequest;
use MVC\Services\HttpResponse;
use MVC\Services\JsonResponse;
use ReflectionClass;
use Tools\Singleton;

class Router {
    private $route;
    private $legacyRoute;

    function __construct() {
        $httpGet = HttpGet::getInstance();

        $this->legacyRoute = preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")->getOrElse("index"));

        $routeParts = explode("/", $this->legacyRoute);

        $count = count($routeParts);
        $routeParts[$count - 1] = "Do" . ucfirst($routeParts[$count - 1]);
        $this->route = implode("/", $routeParts);
    }

    public function route() {

        $this->findRoute();

    }

    private function findRoute() {

        $request = HttpRequest::getInstance();

        $class = str_replace("/", "\\", CONTROLLERS_ROOT . $this->route);
        $method = "do" . ucfirst($request->getMethod());

        try {

            // Reflect controller class
            loadClassOrThrow($class, new DocNotFoundException());
            $reflection = new \ReflectionClass($class);

            // Check for valid reflector
            if ($reflection->getParentClass() === false || $reflection->getParentClass()->getName() !== "MVC\\Controller") {
                throw new \BadFunctionCallException("Incorrect controller");
            }

            // Try to find required method and get parameters
            $params = $reflection->getMethod($method)->getParameters();

            // Inject dependencies
            $dependencies = $this->loadDependencies($params);

            // Create instance of desired controller
            $classInstance = call_user_func([$reflection, "newInstance"]);

            unset($params, $request, $reflection);

            // Execute controller
            call_user_func_array([$classInstance, $method], $dependencies);

        } catch (Exception $e) {

            $this->exceptionRouter($e);

        }

        $response = JsonResponse::getInstance();

        callPrivateMethod($response, "write");

    }

    private function exceptionRouter(Exception $exception) {

        $response = JsonResponse::getInstance();

        if ($exception instanceof ControllerException) {
            $response->setMessage($exception->getMyMessage());
            $response->setData($exception->getMyData());
            $response->setCode(0);
        } else if ($exception instanceof DocNotFoundException)  {
            $response->setResponseCode(404);
            $response->setMessage($exception->getMessage());
            $response->setData($exception->getTrace());
        } else if ($exception instanceof ApplicationException) {
            $response->setMessage($exception->getMessage());
            $response->setData($exception->getTrace());
            $response->setCode(0);
        } else {
            $response->setMessage($exception->getMessage());
            $response->setData($exception->getTrace());
            $response->setCode(0);
            $response->setResponseCode(500);
        }

    }

    private function loadDependencies(array $params) {
        $dependencies = [];
        foreach ($params as $param) {
            /** @var \ReflectionParameter $param */
            if (is_null($param->getClass()) || !$this->isInjectable($param->getClass())) {
                throw new Exception("Object could not be injected");
            }

            if ($this->isSingleton($param->getClass())) {
                $dependencies[] = $param->getClass()->getMethod("getInstance")->invoke(null);
            } else {
                $dependencies[] = $param->getClass()->newInstanceArgs();
            }

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
        $this->outputJSON(1, $message, $data);
    }

    private function outputFailure($message = null, $data = null) {
        $this->outputJSON(0, $message, $data);
    }

    private function outputJSON($code = 1, $message = null, $data = null) {
        header("Content-Type: application/json");
        echo json_encode([
            "status" => $code,
            "message" => $message,
            "data" => $data
        ]);
    }

}