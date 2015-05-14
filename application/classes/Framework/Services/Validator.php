<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:31
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Validator implements Injectable {

    const EMAIL_REGEXP_PATTERN = "~^[\\w\\S]+@[\\w\\S]+\\.[\\w]{2,4}$~";

    /** @var callable[] */
    protected $predicates = [];

    protected $variable = null;

    function __construct($variable, $predicates = []) {
        $this->variable = $variable;
        $this->predicates = $predicates;
    }

    function number() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_numeric($value); });
        return $copy;
    }

    function string() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return is_string($value); });
        return $copy;
    }

    function min_length($length) {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) use ($length) { return strlen($value) >= $length; });
        return $copy;
    }

    function max_length($length) {
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

    function email() {
        $copy = $this->copy();
        $copy->addPredicate(function ($value) { return preg_match(self::EMAIL_REGEXP_PATTERN, $value); });
        return $copy;
    }


    /**
     * @return Validator
     */
    protected function copy() {
        return new Validator($this->variable, $this->predicates);
    }

    /**
     * @param callable $callable
     */
    protected function addPredicate(callable $callable) {
        $this->predicates[] = $callable;
    }

    /**
     * @param \Exception $exception
     */
    public function throwOnFail(\Exception $exception) {
        $this->run()->justThrow($exception);
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
            $result = call_user_func_array($predicate, $this->variable);
            if (!$result) {
                return Optional::noValue();
            }
        }
        return Optional::hasValue($this->variable);
    }

}