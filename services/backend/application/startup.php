<?php

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: *");

define("NEW_DIR_RIGHTS", 0770);
define("REG_MAIL", 'no-reply@myownradio.biz');
define("REG_NAME", "The MyOwnRadio Team");

define("START_TIME", microtime(true));

define("APP_ROOT", "application/classes/");
define("CONTROLLERS_ROOT", "Framework/Handlers/");
