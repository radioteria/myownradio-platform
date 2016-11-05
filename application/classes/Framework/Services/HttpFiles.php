<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 18:27
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class HttpFiles implements \ArrayAccess, SingletonInterface, Injectable
{
    use Singleton;

    /**
     * @param $file
     * @return Optional
     */
    public function getFile($file)
    {
        return Optional::ofEmpty(@$_FILES[$file]);
    }

    /**
     * @return Optional
     */
    public function getFirstFile()
    {
        $first = reset($_FILES);
        return Optional::ofDeceptive($first);
    }

    /**
     * @param callable $callback
     */
    public function each(callable $callback)
    {
        foreach (array_values($_FILES) as $file) {
            call_user_func($callback, $file);
        }
    }

    public function map(callable $callback)
    {
        return array_map($callback, array_values($_FILES));
    }

    /**
     * @param $offset
     * @return Optional
     */
    public function __get($offset)
    {
        return $this->getFile($offset);
    }

    /**
     * @param mixed $offset
     * @return boolean
     */
    public function offsetExists($offset)
    {
        return isset($_FILES[$offset]);
    }

    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        $this->getFile($offset);
    }

    /**
     * @link http://php.net/manual/en/arrayaccess.offsetset.php
     * @param mixed $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet($offset, $value)
    {
    }

    /**
     * @link http://php.net/manual/en/arrayaccess.offsetunset.php
     * @param mixed $offset
     * @return void
     */
    public function offsetUnset($offset)
    {
    }
}
