<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 21.05.15
 * Time: 19:16
 */

namespace Business\Validator\Entity;


use Business\Validator\ValidatorException;

/**
 * Class UserValidatorException
 * @package Business\Validator\Entity
 */
class UserValidatorException extends ValidatorException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }

    public static function newIncorrectLoginLength() {
        return self::tr("VALIDATOR_USER_LOGIN_LENGTH", [
            UserValidator::$LOGIN_MIN_LENGTH, UserValidator::$LOGIN_MAX_LENGTH
        ]);
    }

    public static function newIncorrectLoginChars() {
        return self::tr("VALIDATOR_USER_LOGIN_CHARS");
    }

    public static function newLoginUnavailable() {
        return self::tr("VALIDATOR_USER_LOGIN_UNAVAILABLE");
    }

    public static function newIncorrectNameLength() {
        return self::tr("VALIDATOR_USER_NAME_LENGTH", [ 0, UserValidator::$NAME_MAX_LENGTH ]);
    }

    public static function newInfoTooLong() {
        return self::tr("VALIDATOR_USER_INFO_LENGTH", [ 0, UserValidator::$INFO_MAX_LENGTH ]);
    }

    public static function newIncorrectEmail() {
        return self::tr("VALIDATOR_USER_EMAIL_FORMAT");
    }

    public static function newUnavailableEmail() {
        return self::tr("VALIDATOR_USER_EMAIL_UNAVAILABLE");
    }
}