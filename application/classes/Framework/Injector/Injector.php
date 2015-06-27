<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 24.02.15
 * Time: 22:23
 */

namespace Framework\Injector;


use Framework\Services\Http\HttpParameter;
use Framework\Services\HttpRequest;
use Tools\Optional;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class Injector implements Injectable, SingletonInterface {

    use Singleton;

    /**
     * @param \ReflectionParameter $class
     * @return mixed|object
     * @throws \Exception
     */
    public function injectByClass($class) {
        if (is_null($class->getClass())) {
            $arg = $class->getName();
            return HttpRequest::getInstance()->getParameterOrFail($arg);
        } else if ($class->getClass()->getName() === Optional::className()) {
            $arg = $class->getName();
            return HttpRequest::getInstance()->getParameter($arg);
        } else if ($class->getClass()->getName() === Option::class) {
            $arg = $class->getName();
            return HttpParameter::getInstance()->get($arg);
        }
        if (!$class->getClass()->implementsInterface("Framework\\Injector\\Injectable")) {
            throw new InjectorException("Object could not be injected");
        }
        if ($class->getClass()->implementsInterface("Tools\\SingletonInterface")) {
            return $class->getClass()->getMethod("getInstance")->invoke(null);
        } else {
            return $class->getClass()->newInstanceArgs();
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
            $array[] = $this->injectByClass($argument);
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

    public static function run($callable) {
        return self::getInstance()->call($callable);
    }
} 