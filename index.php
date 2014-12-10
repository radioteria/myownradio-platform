<?php

require_once "application/startup.php";

$baseMemory = memory_get_usage();
$sTime = microtime(true);

user::loginBySession();

if(application::getMethod() === "POST")
{
    header("Content-Type: application/json; charset=UTF-8");
}

try 
{
    $ctrl = new router(); $ctrl->start(); unset($ctrl);
}
catch(morException $ex)
{
    echo $ex->getErrorPage();
}

$newMemory = memory_get_usage();

misc::writeDebug(sprintf("%s: memory(%0.2f MB) time(%0.2f s)", 
        application::get("route", "", REQ_STRING), 
        ($newMemory - $baseMemory) / 1000000, microtime(true) - $sTime));

