<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 14:31
 */

namespace Framework\Services;


use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class Validator implements Injectable, SingletonInterface {
    use Singleton;
}