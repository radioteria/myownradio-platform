<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:32
 */

namespace Framework\Services\Http;


use Framework\Injector\Injectable;
use Tools\Functional\MapSupport;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpPut extends MapSupport implements SingletonInterface, Injectable {

    use Singleton;

    private $_PUT;

    public function __construct() {
        parse_str(file_get_contents("php://input"), $this->_PUT);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isDefined($key) {
        return array_key_exists($key, $this->_PUT);
    }

    /**
     * @param string $key
     * @return mixed
     */
    protected function getValue($key) {
        return $this->_PUT[$key];
    }

}