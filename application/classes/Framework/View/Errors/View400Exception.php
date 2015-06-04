<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 13.05.15
 * Time: 12:48
 */

namespace Framework\View\Errors;


class View400Exception extends ViewException {
    function __construct($message = null) {
        parent::__construct($message, 400);
    }

} 