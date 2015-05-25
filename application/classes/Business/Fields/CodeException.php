<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 16:44
 */

namespace Business\Fields;


use Framework\Exceptions\ControllerException;

class CodeException extends ControllerException {
    public static function newCodeIncorrect() {
        return self::tr("ERROR_CODE_INCORRECT");
    }
} 