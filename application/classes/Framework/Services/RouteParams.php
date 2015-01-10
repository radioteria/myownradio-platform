<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 10.01.15
 * Time: 11:43
 */

namespace Framework\Services;


use Framework\Exceptions\ControllerException;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class RouteParams extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    private static $params;

    static function setData($params) {
        self::$params = $params;
    }

    /**
     * @param $key
     * @return Optional
     */
    public function getParameter($key) {
        return Optional::ofEmpty(@self::$params[$key]);
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getRequired($key) {
        return $this->getParameter($key)
            ->getOrElseThrow(ControllerException::noArgument($key));
    }

} 