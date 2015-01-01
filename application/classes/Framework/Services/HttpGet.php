<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 17:50
 */

namespace Framework\Services;

use Framework\Exceptions\ControllerException;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class HttpGet
 * @package MVC\Services
 */
class HttpGet extends HttpRequestAdapter implements SingletonInterface, Injectable {

    use Singleton;

    public function getParameter($key) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_GET, $key));
    }

    public function getRequired($key) {
        return $this->getParameter($key)
            ->getOrElseThrow(ControllerException::noArgument($key));
    }

}