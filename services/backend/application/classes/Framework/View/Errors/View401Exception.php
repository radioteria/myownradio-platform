<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.03.15
 * Time: 9:21
 */

namespace Framework\View\Errors;


use Framework\Services\HttpRequest;
use Framework\Services\TwigTemplate;

class View401Exception extends ViewException {
    function __construct() {
        $this->code = 404;
        $this->body = TwigTemplate::getInstance()->renderTemplate("error_401.tmpl", [
            "uri" => HttpRequest::getInstance()->getRequestUri()
        ]);
    }
}