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
        if (!$class->implementsInterface("Framework\\Services\\Injectable")) {
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

    /**
     * @param array $arguments
     * @return array
     */
    public function injectByFunctionArguments(array $arguments) {
        $array = [];
        foreach ($arguments as $argument) {
            $array[] = $this->injectByClass($argument->getClass());
        }
        return $array;
    }

    /**
     * @param $callable
     * @return mixed
     * @throws \Exception
     */
    public function call($callable) {
        if (is_array($callable) && count($callable) == 2) {
            $reflection = new \ReflectionMethod($callable[0], $callable[1]);
            return $reflection->invokeArgs($callable[0],
                $this->injectByFunctionArguments($reflection->getParameters()));
        } else if (is_callable($callable) || is_string($callable)) {
            $reflection = new \ReflectionFunction($callable);
            return $reflection->invokeArgs(
                $this->injectByFunctionArguments($reflection->getParameters()));
        } else {
            throw new \Exception("Wrong type of argument");
        }
    }
} 