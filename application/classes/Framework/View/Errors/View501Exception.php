<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 27.03.15
 * Time: 9:17
 */

namespace Framework\View\Errors;


use Framework\Services\TwigTemplate;

class View501Exception extends ViewException {
    function __construct() {
        $this->code = 501;
        $this->body = TwigTemplate::getInstance()->renderTemplate("error_501.tmpl", []);
    }
} 