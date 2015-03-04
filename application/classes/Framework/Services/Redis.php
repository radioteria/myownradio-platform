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
    private $redis, $digest = [];

    function __construct() {
        $this->redis = new \Redis();
        $this->redis->connect("localhost");
    }

    /**
     * @param string $key
     * @param mixed $object
     */
    public function putObject($key, $object) {
        $serialized = serialize($object);
        /* Update object only if it was modified */
        if (empty($this->digest[$key]) || $this->digest[$key] != md5($serialized)) {
            $this->digest[$key] = md5($serialized);
            $this->redis->hSet(Defaults::REDIS_OBJECTS_KEY, $key, $serialized);
        }
    }

    /**
     * @param string $key
     * @return Optional
     */
    public function getObject($key) {

        if (!$this->redis->hExists(Defaults::REDIS_OBJECTS_KEY, $key)) {
            return Optional::noValue();
        }

        $raw = $this->redis->hGet(Defaults::REDIS_OBJECTS_KEY, $key);
        $this->digest[$key] = md5($raw);

        return Optional::hasValue(unserialize($raw));

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
        $this->redis->multi();
        $object = $this->getObject($key)->getCheckType($constructor);
        if (false !== call_user_func_array($callable, [&$object])) {
            $this->putObject($key, $object);
        }
        $this->redis->exec();
        return $this;
    }

    /**
     * @param $key
     */
    public function clearObject($key) {
        $this->redis->hDel(Defaults::REDIS_OBJECTS_KEY, $key);
        unset($this->digest[$key]);
    }

} 