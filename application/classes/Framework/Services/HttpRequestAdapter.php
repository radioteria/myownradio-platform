<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 01.01.15
 * Time: 14:50
 */

namespace Framework\Services;


use Tools\Optional;

abstract class HttpRequestAdapter implements \ArrayAccess {
    /**
     * @param $offset
     * @return Optional
     */
    public function __get($offset) {
        return $this->getParameter($offset);
    }

    /**
     * @link http://php.net/manual/en/arrayaccess.offsetexists.php
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return boolval(FILTER_INPUT(INPUT_POST, $offset));
    }

    /**
     * @link http://php.net/manual/en/arrayaccess.offsetget.php
     * @param mixed $offset
     * @return Optional
     */
    public function offsetGet($offset) {
        return $this->getParameter($offset);
    }

    /**
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value) {

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Offset to unset
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset <p>
     * The offset to unset.
     * </p>
     * @return void
     */
    public function offsetUnset($offset) {

    }

    /**
     * @param $key
     * @return Optional mixed
     */
    public function __invoke($key) {
        return $this->getParameter($key);
    }

} 