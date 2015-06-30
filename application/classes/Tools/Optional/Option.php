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
abstract class Option implements \IteratorAggregate, \JsonSerializable {

    use OptionMixin;

    public abstract function isEmpty();

    public abstract function get();

    public abstract function getIterator();

    public abstract function nonEmpty();

    public abstract function getOrElse($other);

    public abstract function orFalse();

    public abstract function orZero();

    public abstract function orNull();

    public abstract function orEmpty();

    public abstract function orCall($callable);

    public abstract function orElse(Option $alternative);

    public abstract function orThrow($exception, ...$args);

    public abstract function map($callable);

    public abstract function flatMap($callable);

    public abstract function filter($predicate);

    public abstract function filterNot($predicate);

    public abstract function then($callable, $otherwise = null);

    public abstract function select($value);

    public abstract function selectInstance($object);

    /**
     * @return Option
     */
    public static function None() {
        return None::instance();
    }

    /**
     * @param $value
     * @return Option
     */
    public static function Some($value) {
        return new Some($value);
    }


    function jsonSerialize() {
        return $this->get();
    }

}

/**
 * @return None
 */
function None() {
    return None::instance();
}

/**
 * @param $value
 * @return Some
 */
function Some($value) {
    return new Some($value);
}