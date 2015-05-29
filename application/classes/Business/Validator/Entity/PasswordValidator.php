<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 25.05.15
 * Time: 15:56
 */

namespace Business\Validator\Entity;


use Business\Validator\Validator;
use Framework\Preferences;

class PasswordValidator implements EntityValidator {

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
            ->length(Preferences::getSetting("validator", "user.password.min"),
                Preferences::getSetting("validator", "user.password.max"))
            ->throwOnFail(PasswordValidatorException::newBadPasswordLength());
    }

}