<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.04.15
 * Time: 11:28
 */

namespace Framework\Services\Locale;


use Exception;
use Framework\Exceptions\ApplicationException;

class LocaleException extends ApplicationException {
    public function __construct($message = "", $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
    }
}