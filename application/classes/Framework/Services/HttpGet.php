<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 17:50
 */

namespace Framework\Services;

use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class HttpGet
 * @package MVC\Services
 */
class HttpGet implements \ArrayAccess, SingletonInterface, Injectable {

    use Singleton;

    public function getParameter($key) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_GET, $key));
    }

    /**
     * @param $offset
     * @return Optional
     */
    public function __get($offset) {
        return $this->getParameter($offset);
    }

    /**
     * @param mixed $offset
     * @return boolean true on success or false on failure.
     */
    public function offsetExists($offset) {
        return boolval(FILTER_INPUT(INPUT_GET, $offset));
    }

    /**
     * @param mixed $offset
     * @return Optional
     */
    public function offsetGet($offset) {
        return $this->getParameter($offset);
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {

    }

    /**
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {

    }
}