<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 27.03.15
 * Time: 9:17
 */

namespace Framework\View\Errors;


class View501Exception extends ViewException {
    function __construct($message = null) {
        parent::__construct($message, 501);
    }
} 