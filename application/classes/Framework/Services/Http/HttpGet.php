<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:28
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use Tools\Functional\MapSupport;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpGet extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return array_key_exists($key, $_GET);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return $_GET[$key];
    }

}