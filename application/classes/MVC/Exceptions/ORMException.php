<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 9:47
 */

namespace MVC\Exceptions;


use Exception;

class ORMException extends ApplicationException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}