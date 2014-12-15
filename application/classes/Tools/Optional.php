<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.12.14
 * Time: 23:17
 */

namespace Tools;

use Exception;

class Optional {

    private $value;
    private $predicate;

    /**
     * @param $value
     * @param callable $predicate
     */
    public function __construct($value, callable $predicate) {
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
            throw $exception;
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
     * @return mixed
     */
    public function getRaw() {
        return $this->value;
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function getOrElseCallback(callable $callable) {
        return $this->test() ? $this->value : call_user_func($callable);
    }

    /**
     * @return string
     */
    public function getOrElseEmpty() {
        return $this->test() ? $this->value : "";
    }

    /**
     * @param Exception $exception
     * @throws Exception
     * @return $this
     */
    public function justThrow(Exception $exception) {
        if (!$this->test()) {
            throw $exception;
        }
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function then(callable $callable) {
        if ($this->test()) {
            call_user_func_array($callable, [&$this->value]);
        }
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this
     */
    public function otherwise(callable $callable) {
        if (!$this->test()) {
            call_user_func_array($callable, [&$this->value]);
        }
        return $this;
    }

    /**
     * @return boolean
     */
    public function validate() {
        return $this->test();
    }

    /**
     * @return boolean
     */
    private function test() {
        return boolval(call_user_func($this->predicate, $this->value));
    }

    /*---------------------------------------------------------------*/
    /*                    Static Fabric Methods                      */
    /*---------------------------------------------------------------*/

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must not be a null
     */
    public static function ofNull($value) {
        return new self($value, function ($v) { return !is_null($v); });
    }

    /**
     * @param $value
     * @param callable $callback
     * @return Optional
     * This constructor is alias for new Optional(...)
     */
    public static function of($value, callable $callback) {
        return new self($value, $callback);
    }

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must not be empty
     */
    public static function ofEmpty($value) {
        return new self($value, function ($v) { return !empty($v); });
    }

    /**
     * @param $value
     * @return Optional
     * Use this variable if your variable must be an number
     */
    public static function ofNumber($value) {
        return new self($value, function ($v) { return is_numeric($v); });
    }

    /**
     * @param $value
     * @param $object
     * @return Optional
     * Use this constructor if $value must be an instance of $object
     */
    public static function ofObject($value, $object) {
        return new self($value, function ($v) use ($object) { return $v instanceof $object; });
    }

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must be a positive number
     */
    public static function ofPositiveNumber($value) {
        return new self($value, function ($v) { return is_numeric($v) && $v > 0; });
    }

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must not be a false
     */
    public static function ofDeceptive($value) {
        return new self($value, function ($v) { return $v !== false; });
    }

    /**
     * @param $filePath
     * @return Optional
     * Use this constructor if your variable must be an existent file
     */
    public static function ofFile($filePath) {
        return new self($filePath, function ($file) { return file_exists($file); });
    }

    /**
     * @return Optional
     */
    public static function bad() {
        return self::ofNull(null);
    }

    /**
     * @return string
     */
    public function __toString() {
        return "Optional:" . ($this->test() ? "true" : "false");
    }

}