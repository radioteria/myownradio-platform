<?php

define("NEW_DIR_RIGHTS", 0770);
define("REG_MAIL", 'noreply@myownradio.biz');

define("REQ_INT", 'int');
define("REQ_STRING", 'string');
define("REQ_BOOL", 'bool');

define("START_TIME", microtime(true));
define("APP_ROOT", "application/classes/");

define("CONTROLLERS_ROOT", "MVC/Controllers/");

putenv("PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/games:/usr/local/sbin:/usr/local/bin:/home/admin/bin");

spl_autoload_register("loadClass");

function loadClass($class_name) {
    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
    include $filename;
}

function loadClassOrThrow($class_name, Exception $exception) {
    $filename = APP_ROOT . str_replace("\\", "/", $class_name) . '.php';
    if (file_exists($filename)) {
        include APP_ROOT . $filename;
    } else {
        throw $exception;
    }
}

/*
function find_file_recursive ($filename, $location)
{
    if(!file_exists($location)) { throw new Exception("Directory not found!"); }
    
    $dir_handle = opendir($location);
    
    if(!$dir_handle) { throw new Exception("IO Error!"); }
    
    while($file = readdir($dir_handle))
    {
        if($file === "." || $file === "..")
        {
            continue;
        }
        if($file === $filename)
        {
            unset($dir_handle);
            return $location . "/" . $file;
        }
        if(is_dir($location . "/" . $file))
        {
            try
            {
                return find_file_recursive($filename, $location . "/" . $file);
            }
            catch(Exception $ex) 
            {
                
            }
        }
    }
    
    throw new Exception("File not found!");
}*/