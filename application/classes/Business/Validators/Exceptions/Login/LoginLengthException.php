<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:18
 */

namespace Business\Validators\Exceptions\Login;


use Framework\Preferences;
use Framework\Services\Locale\I18n;

class LoginLengthException extends LoginException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_USER_LOGIN_LENGTH", [
            Preferences::getSetting("validator", "user.login.min"),
            Preferences::getSetting("validator", "user.login.max")
        ]));
    }
}