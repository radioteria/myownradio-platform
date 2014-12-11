<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.12.14
 * Time: 23:17
 */

class Optional {

    private $value;
    private $predicate;

    /**
     * @param $value
     * @param Callable $predicate
     */
    public function __construct($value, Callable $predicate) {
        $this->value = $value;
        $this->predicate = $predicate;
    }

    /**
     * @param Exception $exception
     * @return mixed
     * @throws Exception
     */
    public function getOrElseThrow(Exception $exception) {
        if ($this->test()) {
            return $this->value;
        } else {
            throw new $exception;
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrElse($value) {
        return $this->test() ? $this->value : $value;
    }

    /**
     * @return mixed|null
     */
    public function getOrElseNull() {
        return $this->getOrElse(null);
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function getOrElseCallback(Callable $callable) {
        return $this->test() ? $this->value : call_user_func($callable);
    }

    public function getOrElseEmpty() {
        return $this->test() ? $this->value : "";
    }

    public function validate() {
        return $this->test();
    }

    /**
     * @return mixed
     */
    private function test() {
        return call_user_func($this->predicate, $this->value);
    }

    /*---------------------------------------------------------------*/
    /*                    Static Fabric Methods                      */
    /*---------------------------------------------------------------*/

    /**
     * @param $value
     * @return Optional
     */
    public static function ofNull($value) {
        return new Optional($value, function ($v) { return !is_null($v); });
    }

    /**
     * @param $value
     * @param callable $callback
     * @return Optional
     */
    public static function ofCallback($value, Callable $callback) {
        return new Optional($value, $callback);
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function ofEmpty($value) {
        return new Optional($value, function ($v) { return !empty($v); });
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function ofNumber($value) {
        return new Optional($value, function ($v) { return is_numeric($v); });
    }

    /**
     * @param $value
     * @param $object
     * @return Optional
     */
    public static function ofObject($value, $object) {
        return new Optional($value, function ($v) use ($object) { return $v instanceof $object; });
    }

} 