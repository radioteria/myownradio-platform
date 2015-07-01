<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:16
 */

namespace Business\Validators\Exceptions;


use Framework\Exceptions\ControllerException;

class ValidationException extends ControllerException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }
}