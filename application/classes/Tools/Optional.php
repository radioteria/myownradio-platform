<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 11.12.14
 * Time: 23:17
 */

namespace Tools;

use Exception;
use Framework\Object;
use Tools\Optional\Option;

/**
 * Class Optional
 * @package Tools
 * @deprecated
 */
class Optional {

    use Object;

    /**
     * @var Option
     */
    private $option;

    private static $empty = null;

    /**
     * @param Object $value
     * @param callable|boolean $predicate
     */
    public function __construct($value, $predicate) {

        if (is_bool($predicate)) {
            if ($predicate) {
                $this->option = Option::Some($value);
            } else {
                $this->option = Option::None();
            }
        } else if (is_callable($predicate)) {

            if (call_user_func($predicate, $value)) {
                $this->option = Option::Some($value);
            } else {
                $this->option = Option::None();
            }

        } else {
            $this->option = Option::None();
        }

    }

    /**
     * @param $args
     * @return mixed|null
     * @throws Exception
     * @throws \ReflectionMethod
     * @throws mixed
     */
    public function __invoke(...$args) {

        if (count($args) == 0) {
            return $this->get();
        }

        if ($args[0] instanceof Exception) {
            return $this->getOrElseThrow($args[0]);
        }

        if (is_callable($args[0])) {
            return $this->orElseCall($args[0]);
        }

        return $this->getOrElse($args[0]);

    }

    /**
     * @param $exception
     * @param $args
     * @return mixed
     * @throws
     * @throws Exception
     * @throws \ReflectionMethod
     */
    public function getOrElseThrow($exception, ...$args) {

        return $this->option->orThrow($exception, ...$args);

    }

    /**
     * @param $value
     * @return mixed
     */
    public function getOrElse($value) {
        return $this->option->getOrElse($value);
    }

    public function getCheckType($escape) {
        if (gettype($this->option->get()) == gettype($escape)) {
            return Option::Some($this->option->get());
        } else {
            return Option::None();
        }
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
        return $this->option->get();
    }

    /**
     * @param callable $callable
     * @return mixed
     */
    public function orElseCall(callable $callable) {
        return $this->option->orCall($callable);
    }

    /**
     * @return mixed
     */
    public function getOrElseEmpty() {
        return $this->getOrElse("");
    }

    /**
     * @param Exception $exception
     * @throws Exception
     * @return $this
     */
    public function justThrow(Exception $exception) {
        $this->option->orThrow($exception);
        return $this;
    }

    /**
     * @param callable $callable
     * @return $this|mixed
     */
    public function then(callable $callable) {
        $this->option->then($callable);
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
    public function notEmpty() {
        return $this->test();
    }

    /**
     * @return boolean
     */
    private function test() {
        return $this->option->nonEmpty();
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
        return new self($value, !is_null($value));
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function ofZeroable($value) {
        return new self($value, intval($value) > 0);
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
            if (is_array($v) && count($v) == 0) return false;
            if (is_string($v) && strlen($v) == 0) return false;
            return true;
        });
    }

    /**
     * @param $value
     * @return Optional
     * Use this variable if your variable must be an number
     */
    public static function ofNumber($value) {
        return new self($value, is_numeric($value));
    }

    /**
     * @param $value
     * @param $object
     * @return Optional
     * Use this constructor if $value must be an instance of $object
     */
    public static function ofObject($value, $object) {
        return new self($value, $value instanceof $object);
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function ofArray($value) {
        return new self($value, is_array($value));
    }

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must be a positive number
     */
    public static function ofPositiveNumber($value) {
        return new self($value, is_numeric($value) && $value > 0);
    }

    /**
     * @param $value
     * @return Optional
     * Use this constructor if your variable must not be a false
     */
    public static function ofDeceptive($value) {
        return new self($value, $value !== false);
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
        if (is_null(self::$empty)) {
            self::$empty = new self(null, false);
        }
        return self::$empty;
    }

    /**
     * @param $value
     * @return Optional
     */
    public static function hasValue($value) {
        return new self($value, true);
    }

    /**
     * @return string
     */
    public function __toString() {
        return $this->option->__toString();
    }

    /**
     * @return mixed
     */
    public function getOrElseFalse() {
        return $this->getOrElse(false);
    }

    public function getOrElseZero() {
        return $this->getOrElse(0);
    }

    /**
     * @param $name
     * @param $arguments
     * @return Option
     */
    public function __call($name, $arguments) {
        return $this->option->__call($name, $arguments);
    }

    /**
     * @param $name
     * @return Option
     */
    function __get($name) {
        return $this->option->__get($name);
    }



}
