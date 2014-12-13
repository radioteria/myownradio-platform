<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 23:15
 */

namespace MVC\Exceptions;


class UnauthorizedException extends ControllerException {

    function __construct() {
        parent::__construct("You are unauthorized to access this resource");
    }

}