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

function logger($message) {
    $path = "/usr/local/myownradio/logs/rest-server.log";

    $file = fopen($path, "a+");
    fprintf($file, "%s %s\n", date("d-m-Y, h:i:s"), $message);
    fclose($file);
}

