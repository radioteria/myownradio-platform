<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:24
 */

namespace Framework\Exceptions;


class UnauthorizedException extends ControllerException {

    function __construct($message = null, $data = null) {
        parent::__construct($message, $data);
    }

    static function noAccess() {
        return new self("You aren't authorized to access this resource");
    }

    static function wrongLogin() {
        return new self("Incorrect login or password");
    }

    static function noUserExists($id) {
        return new self("User with id %d not exists", $id);
    }

}