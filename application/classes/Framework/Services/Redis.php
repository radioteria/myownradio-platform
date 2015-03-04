<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:09
 */

namespace Framework\Services;


use Framework\Defaults;
use Framework\Injector\Injectable;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class Redis implements SingletonInterface, Injectable {

    use Singleton;

    /** @var \Redis $redis */
    private $redis;

    function __construct() {
        $this->redis = new \Redis();
        $this->redis->connect("localhost");
    }

    /**
     * @param string $key
     * @param mixed $object
     * @internal param null $expire
     */
    public function putObject($key, $object) {
        $serialized = serialize($object);
        $this->redis->hSet(Defaults::REDIS_OBJECTS_KEY, $key, $serialized);
    }

    /**
     * @param string $key
     * @return Optional
     */
    public function getObject($key) {
        if (!$this->redis->hExists(Defaults::REDIS_OBJECTS_KEY, $key)) {
            return Optional::noValue();
        }
        return Optional::hasValue(
            unserialize($this->redis->hGet(Defaults::REDIS_OBJECTS_KEY, $key))
        );
    }

    /**
     * @param $key
     * @return bool
     */
    public function isObjectExists($key) {
        return $this->redis->hExists(Defaults::REDIS_OBJECTS_KEY, $key);
    }

    /**
     * @param $key
     * @param callable $callable
     * @param mixed $constructor
     * @return $this
     */
    public function applyObject($key, $callable, $constructor = null) {
        $object = $this->getObject($key)->getCheckType($constructor);
        if (false !== call_user_func_array($callable, [&$object])) {
            $this->putObject($key, $object);
        }
        return $this;
    }

    /**
     * @param $key
     */
    public function clearObject($key) {
        $this->redis->hDel(Defaults::REDIS_OBJECTS_KEY, $key);
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function putTemp($key, $value) {
        $this->redis->hPut(Defaults::REDIS_ELEMENTS_KEY, $key, $value);
        return $this;
    }

    /**
     * @param $key
     * @return Optional
     */
    public function getTemp($key) {
        if (!$this->redis->hExists(Defaults::REDIS_ELEMENTS_KEY, $key)) {
            return Optional::noValue();
        }
        return Optional::hasValue(
            $this->redis->hGet(Defaults::REDIS_ELEMENTS_KEY, $key)
        );
    }

    /**
     * @param $key
     * @param int $by
     */
    public function increaseTemp($key, $by = 1) {
        $this->redis->hIncrBy(Defaults::REDIS_ELEMENTS_KEY, $key, $by);
    }

} 