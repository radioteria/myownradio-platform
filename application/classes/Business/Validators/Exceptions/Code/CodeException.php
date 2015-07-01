<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 16:15
 */

namespace Business\Validators\Exceptions\Code;


use Business\Validators\Exceptions\ValidationException;

class CodeException extends ValidationException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }
}