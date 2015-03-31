<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 9:47
 */

namespace Framework\Services\ORM\Exceptions;


use Exception;
use Framework\Exceptions\ApplicationException;

class ORMException extends ApplicationException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}