<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 18:33
 */

namespace MVC;

use Exception;
use MVC\Exceptions\ControllerException;
use MVC\Exceptions\DocNotFoundException;
use MVC\Exceptions\NotImplementedException;
use MVC\Services\HttpGet;
use MVC\Services\HttpRequest;
use MVC\Services\HttpResponse;
use MVC\Services\JsonResponse;
use ReflectionClass;
use Tools\Module;
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

        try {
            $this->findRoute();
        } catch (DocNotFoundException $exception) {
            header("HTTP/1.1 404 Not Found");
            $this->outputFailure("Requested resource not found on this server");
        } catch (ControllerException $exception) {
            $this->outputFailure($exception->getMyMessage(), $exception->getMyData());
        } catch (\ReflectionException $exception) {
            header("HTTP/1.1 400 Bad request");
            $this->outputFailure("Bad request", $exception->getTrace());
        } catch (NotImplementedException $exception) {
            header("HTTP/1.1 501 Not implemented");
            $this->outputFailure("Method not implemented");
        } catch (\Exception $exception) {
            header("HTTP/1.1 505 Internal Server Error");
            $this->outputFailure($exception->getMessage(), $exception->getTrace());
        }

    }

    private function findRoute() {

        $request = HttpRequest::getInstance();

        $class = str_replace("/", "\\", CONTROLLERS_ROOT . $this->route);
        $method = "do" . ucfirst($request->getMethod());

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

        // Execute controller
        call_user_func_array([$classInstance, $method], $dependencies);


        $response = JsonResponse::getInstance();
        $reflection = new ReflectionClass($response);

        $msg = $reflection->getProperty("message");
        $msg->setAccessible(true);

        $data = $reflection->getProperty("data");
        $data->setAccessible(true);

        $this->outputOK($msg->getValue($response), $data->getValue($response));

    }

    private function loadDependencies(array $params) {
        $dependencies = [];
        foreach ($params as $param) {
            if (is_null($param->getClass()) || !$this->isInjectable($param->getClass())) {
                throw new Exception("Object could not be injected");
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