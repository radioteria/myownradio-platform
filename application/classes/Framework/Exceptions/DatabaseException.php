<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 24.12.14
 * Time: 17:39
 */

namespace Framework\Exceptions;


class DatabaseException extends ControllerException {
    function __construct($message = null, $data = null, $code = 200) {
        parent::__construct($message, $data, $code);
    }
}