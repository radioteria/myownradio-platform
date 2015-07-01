<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:27
 */

namespace Business\Validators\Exceptions\Password;


use Framework\Preferences;
use Framework\Services\Locale\I18n;

class PasswordLengthException extends PasswordException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_PASSWORD_LENGTH", [
            Preferences::getSetting("validator", "user.password.min"),
            Preferences::getSetting("validator", "user.password.max")
        ]));
    }
}