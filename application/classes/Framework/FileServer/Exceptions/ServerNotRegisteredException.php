<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 9:21
 */

namespace Framework\FileServer\Exceptions;


use Exception;

class ServerNotRegisteredException extends FileServerException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}