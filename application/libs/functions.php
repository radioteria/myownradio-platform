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

function callDependencyInjection($object, ReflectionMethod $method) {
    $method->setAccessible(true);
    $args = [];
    foreach ($method->getParameters() as $param) {

        /** @var \ReflectionParameter $param */
        if (!$param->getClass()->implementsInterface("Framework\\Services\\Injectable")) {
            throw new Exception("Object could not be injected");
        }

        if ($param->getClass()->implementsInterface("Tools\\SingletonInterface")) {
            $args[] = $param->getClass()->getMethod("getInstance")->invoke(null);
        } else {
            $args[] = $param->getClass()->newInstanceArgs();
        }

    }
    return $method->invokeArgs($object, $args);
}

function logger($message) {
    $path = "/usr/local/myownradio/logs/rest-server.log";

    $file = fopen($path, "a");
    fprintf($file, "%s %s\n", date("d-m-Y, h:i:s"), $message);
    fclose($file);
    flush();
}

function camelToUnderscore($input) {
    preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
    $ret = $matches[0];
    foreach ($ret as &$match) {
        $match = $match == strtoupper($match) ? strtolower($match) : lcfirst($match);
    }
    return implode('_', $ret);
}