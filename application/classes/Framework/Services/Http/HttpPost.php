<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:29
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use Tools\Functional\MapSupport;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpPost extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return array_key_exists($key, $_POST);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return $_POST[$key];
    }

}