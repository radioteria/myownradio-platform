<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 14:15
 */

namespace Tools\Functional;


use Tools\Optional\Option;

abstract class MapSupport {

    protected $_provider = null;

    /**
     * @param string $key
     * @return bool
     */
    public abstract function isDefined($key);

    /**
     * @param string $key
     * @return mixed
     */
    protected abstract function getValue($key);

    /**
     * @param $provider
     * @return $this
     */
    public function registerExceptionProvider($provider) {
        $this->_provider = $provider;
        return $this;
    }

    /**
     * @param string $key
     * @return Option
     */
    public function get($key) {
        return ($this->isDefined($key))
            ? Option::Some($this->getValue($key))
            : Option::None();
    }

    /**
     * @param $key
     * @param $alt
     * @return mixed
     */
    public function getOrElse($key, $alt) {
        return ($this->isDefined($key)) ? $this->getValue($key) : $alt;
    }

    /**
     * @param $key
     * @param callable $filter
     * @return mixed
     */
    public function getFiltered($key, callable $filter) {
        $raiser = function () use (&$key) {
            $this->raiseError($key);
        };
        return $this->get($key)->filter($filter)->orCall($raiser);
    }

    /**
     * @param $key
     * @return mixed
     * @throws \Exception
     * @throws mixed
     */
    public function getOrError($key) {
        if ($this->isDefined($key)) {
            return $this->getValue($key);
        } else {
            $this->raiseError($key);
        }
        return null;
    }

    /**
     * @param $key
     * @throws \Exception
     */
    private function raiseError($key) {
        if (is_null($this->_provider)) {
            throw new \Exception($key . " is not defined");
        } else {
            throw call_user_func($this->_provider, $key);
        }
    }

}
