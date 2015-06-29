<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.02.15
 * Time: 22:23
 */

namespace Framework\Injector;


use Framework\Services\Http\HttpParameter;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class Injector implements Injectable, SingletonInterface {

    use Singleton;

    public static function run($callable) {
        return self::getInstance()->call($callable);
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

    /**
     * @param array $arguments
     * @return array
     */
    public function injectByFunctionArguments(array $arguments) {
        $array = [];
        foreach ($arguments as $argument) {
            $array[] = $this->injectByClass($argument);
        }
        return $array;
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
     * @param string $name
     * @return mixed|object
     * @throws \Exception
     */
    public function injectByName($name) {
        $class = new \ReflectionClass($name);
        return $this->injectByClass($class);
    }

    /**
     * @param \ReflectionParameter $class
     * @return mixed|object
     * @throws \Exception
     */
    public function injectByClass($class) {
        if (is_null($class->getClass())) {
            $arg = $class->getName();
            return HttpParameter::getInstance()->getOrError($arg);
        } else if ($class->getClass()->getName() === Option::class) {
            $arg = $class->getName();
            return HttpParameter::getInstance()->get($arg);
        }
        if (!$class->getClass()->implementsInterface(Injectable::class)) {
            throw new InjectorException("Object could not be injected");
        }
        if ($class->getClass()->implementsInterface(SingletonInterface::class)) {
            return $class->getClass()->getMethod("getInstance")->invoke(null);
        } else {
            return $class->getClass()->newInstanceArgs();
        }
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
} 