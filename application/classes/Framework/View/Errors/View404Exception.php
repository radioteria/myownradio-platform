<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.02.15
 * Time: 11:31
 */

namespace Framework\View\Errors;


class View404Exception extends ViewException {

    function __construct($message = null) {
        parent::__construct($message, 404);
    }
}