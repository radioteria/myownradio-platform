<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:34
 */

namespace Business\Validators\Exceptions\Password;


use Framework\Services\Locale\I18n;

class PasswordIncorrectException extends PasswordException {
    public function __construct() {
        parent::__construct(I18n::tr("ERROR_INCORRECT_PASSWORD"));
    }
}