<?php

use Framework\Services\Http\HttpParameter;
use Framework\Services\Locale\I18n;
use Framework\View\Errors\View400Exception;

// Register http parameter exception provider
$httpExceptionProvider = function ($key) {
    return new View400Exception(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", array($key)));
};

HttpParameter::getInstance()->registerExceptionProvider($httpExceptionProvider);

/* Nothing */

