<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 21.05.15
 * Time: 16:08
 */

namespace Business\Validator;


use Framework\Exceptions\ControllerException;

class ValidatorException extends ControllerException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }
}