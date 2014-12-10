<?php

define("NEW_DIR_RIGHTS", 0770);
define("REG_MAIL", 'noreply@myownradio.biz');

define("REQ_INT", 'int');
define("REQ_STRING", 'string');
define("REQ_BOOL", 'bool');

define("START_TIME", microtime(true));

putenv("PATH=/sbin:/bin:/usr/sbin:/usr/bin:/usr/games:/usr/local/sbin:/usr/local/bin:/home/admin/bin");

spl_autoload_register(function ($class_name)
{
    $filename = $class_name . '.php';
    $path = 'application/classes';
    
    try
    {
        include find_file_recursive($filename, $path);
    }
    catch(Exception $ex) 
    {
        return false;
    }
});

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
}