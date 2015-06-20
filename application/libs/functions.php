<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 10:07
 */

/**
 * @param string $string
 * @return \Tools\String
 */
function _S($string = "") {
    return new \Tools\String($string);
}

function callPrivateMethod($class, $method, array $args = []) {
    $reflection = new ReflectionClass($class);
    $method = $reflection->getMethod($method);
    $method->setAccessible(true);
    return $method->invokeArgs($class, $args);
}

/**
 * @param string $message
 */
function logger($message) {
    $path = "/usr/local/myownradio/logs/rest-server.log";

    $file = fopen($path, "a");
    fprintf($file, "%s %s\n", date("d-m-Y, H:i:s"), $message);
    flush();
    fclose($file);
}

/**
 * @param string $input
 * @return string
 */
function camelToUnderscore($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}

/**
 * @param string $input
 * @return string
 */
function underscoreToCamelCase($input) {
    $parts = explode("_", $input);
    foreach ($parts as &$part) {
        $part = ucfirst($part);
    }
    return implode("", $parts);
}

/**
 * @param $array
 * @return \Tools\Optional
 */
function array_first($array) {
    $value = array_shift($array);
    if ($value) {
        return \Tools\Optional::hasValue($value);
    }
    return \Tools\Optional::noValue();
}

/**
 * @param $func
 * @param ...$arg1
 * @return callable
 */
function partial($func, ...$arg1) {
    return function(...$arg2) use (&$func, &$arg1) {
        return $func(...$arg1, ...$arg2);
    };
}

/**
 * @param $expr
 * @param $true
 * @param $false
 * @return mixed
 */
function when($expr, $true, $false) {
    $result = $expr ? $true : $false;
    return is_callable($result) ? $result() : $result;
}

/**
 * @param $exp
 * @return mixed
 */
function call_or_get($exp) {
    return is_callable($exp) ? $exp() : $exp;
}

/**
 * @param ...$func
 * @return mixed|null
 */
function any(...$func) {
    foreach ($func as $f) {
        $result = call_or_get($f);
        if ($result) {
            return $result;
        }
    }
    return null;
}
