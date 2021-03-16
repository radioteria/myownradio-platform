<?php

namespace app;

use Framework\Injector\Injectable;
use Monolog;
use Tools\Singleton;
use Tools\SingletonInterface;

class Logger extends Monolog\Logger implements Injectable, SingletonInterface
{
    use Singleton;

    protected function __construct()
    {
        parent::__construct("backend");

        $this->pushHandler(new Monolog\Handler\ErrorLogHandler());
    }
}
