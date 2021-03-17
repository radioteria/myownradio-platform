<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 11.12.14
 * Time: 23:17
 */

namespace Tools;

use Exception;

class Optional implements \JsonSerializable {

    /** @var Object $value */
    private $value;
    private $predicate;

    /**
     * @param Object $value
     * @param callable $predicate
     */
    public function __construct($value, callable $predicate) {
        $this->value = $value;
        $this->predicate = $predicate;
    }

    /**
     * @param Exception|string $exception
     * @param null $args
     * @throws \Exception
     * @throws string
     * @return mixed
     */
    public function getOrElseThrow($exception, $args = null) {
        if ($this->test()) {
            return $this->value;
        } else {
            if (is_string($exception)) {
                throw new $exception($args);
            } else {
                throw $exception;
            }
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrElse($value) {
        return $this->test() ? $this->value : $value;
    }

    public function getCheckType($escape) {
        if (gettype($this->value) == gettype($escape)) {
            return $this->value;
        }
        return $escape;
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
    public function get() {
        return $this->value;
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function orElseCall(callable $callable) {
        return $this->test() ? $this->value : call_user_func($callable);
    }

    /**
     * @return mixed
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
     * @return $this|mixed
     */
    public function then(callable $callable) {
        if ($this->test()) {
            call_user_func_array($callable, [&$this->value]);
        }
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this|mixed
     */
    public function otherwise(callable $callable) {
        if (!$this->test()) {
            call_user_func($callable);
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
     * @param callable $onSome
     * @param callable $onNone
     * @return mixed
     */
    public function fold(callable $onSome, callable $onNone) {
        if ($this->test()) {
            return $onSome($this->value);
        }
        return $onNone();
    }

    /**
     * @return boolean
     */
    private function test() {
        return boolval(call_user_func($this->predicate, $this->value));
    }

    /*---------------------------------------------------------------*/
    /*                    Static Factory Methods                      */
    /*---------------------------------------------------------------*/

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must not be a null
     */
    public static function ofNullable($value) {
        return new self($value, function ($v) { return !is_null($v); });
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function ofZeroable($value) {
        return new self($value, function ($v) { return intval($v) > 0; });
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
        return new self($value, function ($v) {
            if (is_null($v)) return false;
            if (strlen($v) == 0) return false;
            return true;
        });
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
     */
    public static function ofArray($value) {
        return new self($value, function ($value) { return is_array($value); });
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
    public static function noValue() {
        return new self(null, function () { return false; });
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function hasValue($value) {
        return new self($value, function () { return true; });
    }

    /**
     * @return string
     */
    public function __toString() {
        return "[Optional:" . ($this->test() ? "Has Value" : "Has No Value") . "]";
    }

    /**
     * @return mixed
     */
    public function getOrElseFalse() {
        return $this->getOrElse(false);
    }

    public function jsonSerialize() {
        return ["test" => $this->test() ? "true" : "false", "value" => $this->value];
    }

    public function getOrElseZero() {
        return $this->getOrElse(0);
    }

}