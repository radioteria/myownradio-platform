<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:31
 */

namespace Business\Validator;


use Tools\Optional;
use Tools\Singleton;

class Validator {

    const EMAIL_REGEXP_PATTERN = "~^[\\w\\S]+@[\\w\\S]+\\.[\\w]{2,4}$~";

    /** @var callable[] */
    protected $predicates = [];

    protected $variable = null;

    function __construct($variable, $predicates = []) {
        $this->variable = $variable;
        $this->predicates = $predicates;
    }

    function isNumber() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_numeric($value); });
        return $copy;
    }

    function isString() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_string($value); });
        return $copy;
    }

    function stringOrNull() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_null($value) || is_string($value); });
        return $copy;
    }

    function minLength($length) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($length) { return strlen($value) >= $length; });
        return $copy;
    }

    function maxLength($length) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($length) { return strlen($value) <= $length; });
        return $copy;
    }

    function greaterOrEqual($than) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($than) { return $value >= $than; });
        return $copy;
    }

    function lessOrEqual($than) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($than) { return $value <= $than; });
        return $copy;
    }

    function pattern($pattern) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($pattern) { return preg_match($pattern, $value); });
        return $copy;
    }

    function isExistsInArray($array) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($array) { return array_search($value, $array) !== false; });
        return $copy;
    }

    function isExistsInIterator(\Iterator $iterator) {
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
    function email() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return preg_match(self::EMAIL_REGEXP_PATTERN, $value); });
        return $copy;
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
        $this->run()->justThrow($exception);
        return $this->clear();
    }

    /**
     * @param callable $callable
     */
    public function doOnSuccess(Callable $callable) {
        $this->run()->then($callable);
    }

    /**
     * @return Optional
     */
    public function run() {
        foreach ($this->predicates as $predicate) {
            $result = $predicate($this->variable);
            if (!$result) {
                return Optional::noValue();
            }
        }
        return Optional::hasValue($this->variable);
    }

    /**
     * @return bool
     */
    public function ok() {
        return $this->run()->validate();
    }

}