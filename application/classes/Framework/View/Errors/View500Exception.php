<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 10:11
 */

namespace Framework\View\Errors;


class View500Exception extends ViewException {

    function __construct($message = null) {
        parent::__construct($message, 500);
    }

}