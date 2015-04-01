<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 10:11
 */

namespace Framework\View\Errors;


use Framework\Services\TwigTemplate;

class View500Exception extends ViewException {

    function __construct($message = null) {
        $this->code = 500;
        $this->body = TwigTemplate::getInstance()->renderTemplate("error_500.tmpl", ["message" => $message]);
    }
}