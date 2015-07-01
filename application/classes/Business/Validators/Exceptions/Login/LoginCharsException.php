<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:22
 */

namespace Business\Validators\Exceptions\Login;


use Framework\Services\Locale\I18n;

class LoginCharsException extends LoginException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_USER_LOGIN_CHARS"));
    }
}