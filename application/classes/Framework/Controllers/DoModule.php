<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.12.14
 * Time: 17:24
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Services\HttpPost;
use Framework\Services\HttpRequest;
use Framework\Services\Module\ModuleObject;

class DoModule implements Controller {
    public function doGet(HttpGet $get) {

        $type = $get->getParameter("type")->getOrElseThrow(ControllerException::noArgument("type"));
        $name = $get->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));

        $module = new ModuleObject($name);

        if ($type == "js") {
            header("Content-Type: text/javascript");
            echo $module->getJS();
        }

        if ($type == "css") {
            header("Content-Type: text/css");
            echo $module->getCSS();
        }

        if ($type == "exec") {
            echo $module->executeHtml();
        }

    }

    public function doPost(HttpGet $get) {

        $name = $get->getParameter("name")->getOrElseThrow(ControllerException::noArgument("name"));

        $module = new ModuleObject($name);

        echo $module->executePost();

    }
} 