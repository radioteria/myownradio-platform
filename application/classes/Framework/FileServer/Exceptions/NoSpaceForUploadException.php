<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 12:17
 */

namespace Framework\FileServer\Exceptions;


use Exception;

class NoSpaceForUploadException extends FileServerException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}