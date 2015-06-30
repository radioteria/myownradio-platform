<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:31
 */

namespace Business\Validator;


use Framework\Preferences;
use Tools\Optional\Option;

class Validator {

    const EMAIL_REGEXP_PATTERN = '~^[\w\S]+@[\w\S]+\.[\w]{2,4}$~';

    /** @var callable[] */
    protected $predicates = [];

    protected $variable = null;

    function __construct($variable, $predicates = []) {
        $this->variable = $variable;
        $this->predicates = $predicates;
    }

    /**
     * @param array|callable $callable
     * @return $this
     */
    function predicate($callable) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use (&$callable) {
            return boolval(call_user_func($callable, $value));
        });
        return $copy;
    }

    /**
     * @return $this
     */
    function isNumber() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_numeric($value); });
        return $copy;
    }

    /**
     * @param $that
     * @return $this
     */
    function is($that) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use (&$that) {
            return $value === $that || $value instanceof $that;
        });
        return $copy;
    }

    /**
     * @return $this
     */
    function isString() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_string($value); });
        return $copy;
    }

    /**
     * @return $this
     */
    function isNull() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_null($value); });
        return $copy;
    }

    /**
     * @param $length
     * @return $this
     */
    function minLength($length) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($length) { return strlen($value) >= $length; });
        return $copy;
    }

    /**
     * @param $length
     * @return $this
     */
    function maxLength($length) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($length) { return strlen($value) <= $length; });
        return $copy;
    }

    /**
     * @param $min
     * @param $max
     * @return $this
     */
    function length($min, $max) {
        return $this->minLength($min)->maxLength($max);
    }

    /**
     * @param $than
     * @return $this
     */
    function greaterOrEqual($than) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($than) { return $value >= $than; });
        return $copy;
    }

    /**
     * @param $than
     * @return $this
     */
    function lessOrEqual($than) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($than) { return $value <= $than; });
        return $copy;
    }

    /**
     * @param string $pattern
     * @return $this
     */
    function pattern($pattern) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($pattern) { return preg_match("~$pattern~", $value); });
        return $copy;
    }

    /**
     * @param $array
     * @return $this
     */
    function existsInArray($array) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($array) { return array_search($value, $array) !== false; });
        return $copy;
    }

    /**
     * @param \Iterator $iterator
     * @return $this
     */
    function existsInIterator(\Iterator $iterator) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($iterator) {
            foreach ($iterator as $item) {
                if ($item === $value) {
                    return true;
                }
            }
            return false;
        });
        return $copy;
    }

    /**
     * @return $this
     */
    function isEmail() {
        return $this->pattern(Preferences::getSetting("validator", "email.pattern"));
    }


    /**
     * @return $this
     */
    protected function copy() {
        return new $this($this->variable, $this->predicates);
    }

    /**
     * @return $this
     */
    protected function clear() {
        return new $this($this->variable);
    }

    /**
     * @param callable $callable
     */
    protected function addPredicate(callable $callable) {
        $this->predicates[] = $callable;
    }

    /**
     * @param \Exception $exception
     * @return $this
     */
    public function throwOnFail(\Exception $exception) {
        $this->run()->getOrThrow($exception);
        return $this->clear();
    }

    /**
     * @param callable $callable
     */
    public function doOnSuccess(callable $callable) {
        $this->run()->then($callable);
    }

    /**
     * @return Option
     */
    public function run() {
        foreach ($this->predicates as $predicate) {
            $result = $predicate($this->variable);
            if (!$result) {
                return Option::None();
            }
        }
        return Option::Some($this->variable);
    }

    /**
     * @return bool
     */
    public function ok() {
        return $this->run()->nonEmpty();
    }

}