<?php

use Tools\File;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define("NEW_DIR_RIGHTS", 0770);
define("REG_MAIL", 'no-reply@myownradio.biz');
define("REG_NAME", "The MyOwnRadio Team");

define("APP_ROOT", "application/classes/");
define("CONTROLLERS_ROOT", "Framework/Handlers/");

putenv("PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/local/sbin:/usr/local/bin:/home/admin/bin");

spl_autoload_register(function ($class_name) {
    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
    if (file_exists($filename)) {
        require_once $filename;
    }
});

//function loadClass($class_name) {
//    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
//    if (file_exists($filename)) {
//        require $filename;
//    }
//}

$path = new File("application/startup/");
foreach ($path->getDirContents() as $file) {
    /** @var File $file */
    require_once $file->path();
}
