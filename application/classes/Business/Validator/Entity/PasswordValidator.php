<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 15:56
 */

namespace Business\Validator\Entity;


use Business\Validator\Validator;

class PasswordValidator implements EntityValidator {

    public static $PASSWORD_MIN_LENGTH = 6;
    public static $PASSWORD_MAX_LENGTH = 32;

    private $password;

    public static function validate($password) {
        $validator = new self($password);
        $validator->validateAllFields();
    }

    public function __construct($password) {
        $this->password = $password;
    }

    /**
     * @throws PasswordValidatorException
     */
    public function validateAllFields() {
        (new Validator($this->password))
            ->length(self::$PASSWORD_MIN_LENGTH, self::$PASSWORD_MAX_LENGTH)
            ->throwOnFail(PasswordValidatorException::newBadPasswordLength());
    }

}