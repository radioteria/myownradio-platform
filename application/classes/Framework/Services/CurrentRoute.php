<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.03.15
 * Time: 20:07
 */

namespace Framework\Services;

use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class CurrentRoute implements Injectable, SingletonInterface {

    use Singleton;

    /**
     * @return string
     */
    public function __toString() {
        $httpGet = HttpGet::getInstance();
        return preg_replace('/(\.(html|php)$)|(\/$)/', '', $httpGet->getParameter("route")->getOrElse("index"));
    }
} 