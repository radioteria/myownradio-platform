<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.06.2015
 * Time: 13:29
 */

namespace Tools\Optional;

/**
 * Class Option
 * @package Tools\Optional
 */
abstract class Option implements \ArrayAccess, \IteratorAggregate, \JsonSerializable {

    use OptionMixin;

    public abstract function isEmpty();

    public abstract function get();

    /**
     * @return \Iterator
     */
    public function getIterator() {
        if ($this->nonEmpty())
            yield $this->get();
    }

    public function nonEmpty() {
        return !$this->isEmpty();
    }

    public function getOrElse($other) {
        return ($this->isEmpty()) ? $other : $this->get();
    }

    public function orFalse() {
        return $this->getOrElse(false);
    }

    public function orZero() {
        return $this->getOrElse(0);
    }

    public function orNull() {
        return $this->getOrElse(null);
    }

    public function orEmpty() {
        return $this->getOrElse("");
    }

    public function orCall($callable) {
        return ($this->isEmpty()) ? $callable() : $this->get();
    }

    public function orElse(Option $alternative) {
        return ($this->isEmpty()) ? $alternative : $this;
    }

    public function orThrow($exception, ...$args) {

        if ($this->isEmpty()) {

            if (is_string($exception)) {

                $reflection = new \ReflectionClass($exception);
                $obj = $reflection->newInstanceArgs($args);
                if ($obj instanceof \Exception) {
                    throw $obj;
                } else {
                    throw new OptionException("Invalid exception passed");
                }

            } else if ($exception instanceof \ReflectionMethod && $exception->isStatic()) {
                throw $exception->invokeArgs(null, $args);
            } else if ($exception instanceof \Exception) {
                throw $exception;
            }

        }

        return $this->get();

    }

    /**
     * @param $callable
     * @return Option
     */
    public function map($callable) {
        return $this->isEmpty() ? $this : Some($callable($this->get()));
    }

    /**
     * @param $callable
     * @return None|mixed
     */
    public function flatMap($callable) {
        return $this->isEmpty() ? None() : $callable($this->get());
    }

    /**
     * @param $predicate
     * @return Option
     */
    public function filter($predicate) {
        return ($this->isEmpty() || $predicate($this->get())) ? $this : None();
    }

    /**
     * @param $predicate
     * @return Option
     */
    public function filterNot($predicate) {
        return ($this->isEmpty() || !$predicate($this->get())) ? $this : None();
    }

    /**
     * @param $callable
     * @param null $otherwise
     */
    public function then($callable, $otherwise = null) {
        if ($this->nonEmpty())
            $callable($this->get());
        else if (is_callable($otherwise)) {
            $otherwise();
        }

    }

    /**
     * @return None
     */
    public static function None() {
        return None();
    }

    /**
     * @param $value
     * @return Some
     */
    public static function Some($value) {
        return Some($value);
    }

    /**
     * @param $name
     * @return Option
     */
    function __get($name) {
        return $this->isEmpty() ? None() : Some($this->get()->$name);
    }

    public function offsetExists($offset) {
        throw new \Exception("This feature is not available");
    }

    public function offsetGet($offset) {
        return $this->isEmpty() ? None() : Some($this->get()[$offset]);
    }

    public function offsetSet($offset, $value) {
        throw new \Exception("This feature is not available");
    }


    public function offsetUnset($offset) {
        throw new \Exception("This feature is not available");
    }

    function jsonSerialize() {
        return $this->get();
    }

}

/**
 * @return None
 */
function None() {
    return None::getInstance();
}

/**
 * @param $value
 * @return Some
 */
function Some($value) {
    return new Some($value);
}