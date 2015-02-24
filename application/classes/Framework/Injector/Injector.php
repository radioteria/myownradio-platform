<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.02.15
 * Time: 22:23
 */

namespace Framework\Injector;


use Framework\Services\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class Injector implements Injectable, SingletonInterface {

    use Singleton;

    /**
     * @param \ReflectionClass $class
     * @return mixed|object
     * @throws \Exception
     */
    public function injectByClass($class) {
        if ($class->implementsInterface("Framework\\Services\\Injectable")) {
            throw new \Exception("Object could not be injected");
        }
        if ($class->implementsInterface("Tools\\SingletonInterface")) {
            return $class->getMethod("getInstance")->invoke(null);
        } else {
            return $class->newInstanceArgs();
        }
    }

    /**
     * @param string $name
     * @return mixed|object
     * @throws \Exception
     */
    public function injectByName($name) {
        $class = new \ReflectionClass($name);
        return $this->injectByClass($class);
    }

    /**
     * @param array $names
     * @return array
     * throws \Exception
     */
    public function injectByNameArray(array $names) {
        $array = [];
        foreach ($names as $name) {
            $array[] = $this->injectByName($name);
        }
        return $array;
    }

    /**
     * @param array $classes
     * @return array
     * throws \Exception
     */
    public function injectByClassArray(array $classes) {
        $array = [];
        foreach ($classes as $class) {
            $array[] = $this->injectByClass($class);
        }
        return $array;
    }

    public function call($callable) {
        if (is_array($callable) && count($callable) == 2) {
            return (new \ReflectionMethod($callable[0], $callable[1]))->invoke($callable[0]);
        } else if (is_string($callable)) {
            return (new \ReflectionFunction($callable))->invoke();
        } else {
            throw new \Exception("Wrong type of argument");
        }
    }
} 