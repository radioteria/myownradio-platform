<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 16:15
 */

namespace Business\Validator\Entity;


use Business\Validator\ValidatorException;
use Framework\Preferences;

class PasswordValidatorException extends ValidatorException {

    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }

    public static function newBadPasswordLength() {
        return self::tr("VALIDATOR_PASSWORD_LENGTH", [
            Preferences::getSetting("validator", "user.password.min"),
            Preferences::getSetting("validator", "user.password.max")
        ]);
    }

} 