<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:31
 */

namespace Framework\View\Errors;


class View404Exception extends ViewException {
    function __construct() {
        parent::__construct(404, "application/tmpl/error/404.tmpl", [
            "msg" => "Sorry, but requested document not found on this server.",
            "time" => time()
        ]);
    }
}