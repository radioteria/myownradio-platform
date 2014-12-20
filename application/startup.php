<?php


define("NEW_DIR_RIGHTS", 0770);
define("REG_MAIL", 'no-reply@myownradio.biz');
define("REG_NAME", "The MyOwnRadio Team");

define("START_TIME", microtime(true));

define("APP_ROOT", "application/classes/");
define("CONTROLLERS_ROOT", "Framework/Controllers/");

putenv("PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/games:/usr/local/sbin:/usr/local/bin:/home/admin/bin");

spl_autoload_register("loadClass");

function loadClass($class_name) {
    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
    include $filename;
}

function loadClassOrThrow($class_name, Exception $exception) {
    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
    if (file_exists($filename)) {
        include $filename;
    } else {
        throw $exception;
    }
}
