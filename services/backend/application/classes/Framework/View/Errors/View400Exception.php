<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 12:48
 */

namespace Framework\View\Errors;


use Framework\Services\HttpRequest;
use Framework\Services\TwigTemplate;

class View400Exception extends ViewException {
    function __construct($message = null) {
        $this->code = 400;
        $this->body = TwigTemplate::getInstance()->renderTemplate("error_400.tmpl", [
            "uri" => HttpRequest::getInstance()->getRequestUri(),
            "message" => $message
        ]);
    }
} 