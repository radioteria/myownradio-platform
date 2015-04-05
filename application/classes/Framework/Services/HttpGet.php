<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 17:50
 */

namespace Framework\Services;

use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class HttpGet
 * @package MVC\Services
 */
class HttpGet extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    public function getParameter($key, $filter = FILTER_DEFAULT, $args = null) {
        if (FILTER_INPUT(INPUT_GET, $key) !== null) {
            return Optional::ofEmpty(FILTER_INPUT(INPUT_GET, $key, $filter, $args));
        } else {
            return RouteParams::getInstance()->getParameter($key);
        }
    }

    public function getRequired($key, $filter = FILTER_DEFAULT, $args = null) {
        return $this->getParameter($key, $filter, $args)
            ->getOrElseThrow(ControllerException::noArgument($key));
    }

}