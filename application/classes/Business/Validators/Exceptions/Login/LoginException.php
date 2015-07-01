<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:17
 */

namespace Business\Validators\Exceptions\Login;


use Business\Validators\Exceptions\ValidationException;

class LoginException extends ValidationException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }
}