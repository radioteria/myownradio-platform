<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.03.15
 * Time: 9:21
 */

namespace Framework\View\Errors;


class View401Exception extends ViewException {
    function __construct($message = null) {
        parent::__construct($message, 401);
    }
}