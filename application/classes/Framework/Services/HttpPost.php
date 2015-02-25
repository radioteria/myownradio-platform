<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:26
 */

namespace Framework\Services;

use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpPost extends HttpRequestAdapter implements Injectable, SingletonInterface {

    use Singleton;

    public function getParameter($key) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_POST, $key));
    }

    public function getRequired($key) {
        return $this->getParameter($key)
            ->getOrElseThrow(ControllerException::noArgument($key));
    }


}