<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 11:24
 */

namespace Framework\Exceptions;


use Exception;
use Framework\View\Errors\View500Exception;

class ApplicationException extends Exception {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }

    public static function of($message = "", $code = 0, Exception $previous = null) {
        return new self($message, $code, $previous);
    }

    public static function databaseException() {
        return new View500Exception();
    }
} 