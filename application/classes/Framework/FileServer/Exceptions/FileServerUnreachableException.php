<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 31.03.15
 * Time: 9:41
 */

namespace Framework\FileServer\Exceptions;


use Exception;

class FileServerUnreachableException extends FileServerException{
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}