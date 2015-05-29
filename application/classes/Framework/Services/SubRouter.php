<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 11:05
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Framework\Injector\Injector;
use Framework\Router;
use Tools\Singleton;
use Tools\SingletonInterface;

class SubRouter implements SingletonInterface, Injectable {

    use Singleton;

    private $routes = [];
    private $default = null;

    /**
     * Registers new route into the storage
     * @param string $route Route
     * @param callable|string $callable Callback function or String name of class
     */
    public function addRoute($route, $callable) {

        list($regexp, $keys) = $this->makeRegexp($route);

        $this->routes[$regexp] = [
            "keys" => $keys,
            "action" => $callable
        ];

    }

    public function addRouteRegExp($regexp, $callable) {
        $this->routes[$regexp] = [
            "keys" => [],
            "action" => $callable
        ];
    }

    public function addRoutes(array $routes, $callable) {
        foreach ($routes as $route) {
            $this->addRoute($route, $callable);
        }
    }

    /**
     * @param $callable
     */
    public function defaultRoute($callable) {
        $this->default = $callable;
    }

    /**
     * Removes all registered routes from the storage
     */
    public function cleanAll() {
        $routes = [];
    }

    /**
     * @param $route
     * @return bool
     * @throws \Exception
     */
    public function goMatching($route) {
        foreach ($this->routes as $regexp => $data) {
            if (preg_match($regexp, $route, $matches)) {
                array_shift($matches);
                if (count($data["keys"]) == count($matches)) {
                    $result = array_combine($data["keys"], $matches);
                    RouteParams::setData($result);
                }
                if (is_string($data["action"])) {
                    Router::getInstance()->callRoute($data["action"]);
                } elseif (is_callable($data["action"])) {
                    Injector::getInstance()->call($data["action"]);
                } else {
                    throw new \Exception("Incorrect action format!");
                }
                return true;
            }
        }
        if ($this->default !== null) {
            if (is_string($this->default)) {
                Router::getInstance()->callRoute($this->default);
            } elseif (is_callable($this->default)) {
                Injector::getInstance()->call($this->default);
            } else {
                throw new \Exception("Incorrect action format!");
            }
            return true;
        }
        return false;
    }

    /**
     * @param String $route
     * @return array
     */
    private function makeRegexp($route) {

        $quoteRoute = preg_replace_callback("~(?!:([a-z\\_]+))|(?!&([a-z\\_]+))~", function ($match) {
            return preg_quote($match[0]);
        }, $route);

        $keys = [];


        $quoteParams =

            preg_replace_callback("~&([a-z\\_]+)~", function ($match) use (&$keys) {
                    $keys[] = $match[1];
                    return "(?:(\\d+))";
                },

            preg_replace_callback("~:([a-z\\_]+)~", function ($match) use (&$keys) {
                    $keys[] = $match[1];
                    return "(?:([^\\/]+))";
                }, $quoteRoute));

        return [
            sprintf("~^%s$~", $quoteParams),
            $keys
        ];

    }

} 