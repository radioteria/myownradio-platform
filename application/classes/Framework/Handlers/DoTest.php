<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 28.05.15
 * Time: 12:21
 */

namespace Framework\Handlers;


use Framework\ControllerImpl;
use Framework\Services\Http\HttpGet;
use Framework\Services\Http\HttpParameter;

class DoTest extends ControllerImpl {
    public function doGet(HttpParameter $parameter, HttpGet $get) {

        echo $parameter->get("id");

    }
}

