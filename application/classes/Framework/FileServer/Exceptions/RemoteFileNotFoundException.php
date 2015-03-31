<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 31.03.15
 * Time: 10:18
 */

namespace Framework\FileServer\Exceptions;


use Exception;

class RemoteFileNotFoundException extends FileServerException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}