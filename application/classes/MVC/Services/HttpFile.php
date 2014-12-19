<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:27
 */

namespace MVC\Services;

use Tools\Optional;
use Tools\Singleton;

class HttpFile implements \ArrayAccess {
    use Singleton, Injectable;

    /**
     * @param $file
     * @return Optional
     */
    public function getFile($file) {
        return Optional::ofEmpty(@$_FILES[$file]);
    }

    /**
     * @param callable $callback
     */
    public function each(callable $callback) {
        foreach ($_FILES as $file) {
            call_user_func($callback, $file);
        }
    }

    /**
     * @param $offset
     * @return Optional
     */
    public function __get($offset) {
        return $this->getFile($offset);
    }

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset) {
        return isset($_FILES[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset) {
        $this->getFile($offset);
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
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset) {

    }
}