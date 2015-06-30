<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.06.2015
 * Time: 13:48
 */

namespace Tools\Optional;

/**
 * Class Some
 * @package Tools\Optional
 */
final class Some extends Option {

    private $value;

    public function __construct($value) {
        $this->value = $value;
    }

    public function isEmpty() {
        return false;
    }

    public function get() {
        return $this->value;
    }

    public function __toString() {
        return "Some(" . $this->value . ")";
    }

    public function getIterator() {
        yield $this->get();
    }

    public function nonEmpty() {
        return true;
    }

    public function getOrElse($other) {
        return $this->get();
    }

    public function orFalse() {
        return $this->get();
    }

    public function orZero() {
        return $this->get();
    }

    public function orNull() {
        return $this->get();
    }

    public function orEmpty() {
        return $this->get();
    }

    public function orCall($callable) {
        return $this->get();
    }

    public function orElse(Option $alternative) {
        return $this;
    }

    public function orThrow($exception, ...$args) {
        return $this->get();
    }

    public function map($callable) {
        return new Some($callable($this->get()));
    }

    public function flatMap($callable) {
        $result = $callable($this->get());
        if (!$result instanceof Option) {
            throw new OptionException("Callable passed to .flatMap() must return Option object!");
        }
        return $result;
    }

    public function filter($predicate) {
        return $predicate($this->get()) ? $this : None::instance();
    }

    public function filterNot($predicate) {
        return $predicate($this->get()) ? None::instance() : $this;
    }

    public function then($callable, $otherwise = null) {
        $callable($this->get());
    }

    public function select($value) {
        return $this->get() === $value ? $this : None::instance();
    }

    public function selectInstance($object) {
        return ($this->get() instanceof $object) ? $this : None::instance();
    }

}
