<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 29.06.15
 * Time: 19:06
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use http\Env;
use Tools\Functional\MapSupport;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpHeader extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return Env::getRequestHeader($key) !== null;
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return Env::getRequestHeader($key);
    }

}