<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:26
 */

namespace MVC\Services;

use Tools\Optional;
use Tools\Singleton;

class HttpPost implements \ArrayAccess {
    use Singleton, Injectable;

    public function getParameter($key) {
        return Optional::ofEmpty(FILTER_INPUT(INPUT_POST, $key));
    }

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

}