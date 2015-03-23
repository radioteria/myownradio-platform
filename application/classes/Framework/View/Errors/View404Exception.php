<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:31
 */

namespace Framework\View\Errors;


use Framework\Services\HttpRequest;
use Framework\Services\TwigTemplate;

class View404Exception extends ViewException {
    function __construct() {

    }
    public function render() {
        http_response_code(404);
        TwigTemplate::getInstance()->displayTemplate("error_404.tmpl", [
            "uri" => HttpRequest::getInstance()->getRequestUri()
        ]);
    }
}