<?php

/**
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function config($key, $default = null)
{
    static $config = null;

    $makeValueGetter = function ($key) {
        $path = explode('.', $key);
        $getValue = function ($config, $value) {
            return is_array($config) && array_key_exists($value, $config)
                ? $config[$value]
                : null;
        };
        return function ($config) use ($path, $getValue) {
            return array_reduce($path, $getValue, $config);
        };
    };

    $getConfig = function ($configDir) {
        $absFilePath = function ($file) use ($configDir) {
            return $configDir . DIRECTORY_SEPARATOR . $file;
        };
        $isValidConfigFile = function ($file) {
            return is_file($file) && pathinfo($file, PATHINFO_EXTENSION) == 'php';
        };
        $filesReduce = function ($acc, $file) {
            $filename = pathinfo($file, PATHINFO_FILENAME);
            $config = require $file;
            return array_merge($acc, [$filename => $config]);
        };
        $filesList = scandir($configDir);
        $absoluteList = array_map($absFilePath, $filesList);
        $filteredList = array_filter($absoluteList, $isValidConfigFile);
        return array_reduce($filteredList, $filesReduce, []);
    };

    if (is_null($config)) {
        $config = $getConfig(CONFIG_DIR);
    }

    $getter = $makeValueGetter($key);

    return $getter($config) ?: $default;
}

/**
 * @param string $key
 * @param mixed $default
 * @return mixed
 */
function env($key, $default = null)
{
    return array_key_exists($key, $_ENV) ? $_ENV[$key] : $default;
}
