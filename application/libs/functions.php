<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 10:07
 */

/**
 * Converts text from CP1251 to UTF-8 encoding.
 *
 * @param $chars
 * @return string
 */
function cp1251dec($chars)
{
    $test = @iconv("UTF-8", "CP1252", $chars);
    if (is_null($test)) {
        return $chars;
    } else {
        return iconv("CP1251", "UTF-8", $test);
    }
}

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