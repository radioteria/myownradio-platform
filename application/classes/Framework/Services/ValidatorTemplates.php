<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 21.05.15
 * Time: 11:26
 */

namespace Framework\Services;


use Business\Validator\BusinessValidator;
use Framework\Exceptions\ControllerException;
use Framework\Services\Locale\I18n;

trait ValidatorTemplates {

    /**
     * @param $password
     * @throws ControllerException
     */
    final public static function validatePassword($password) {

        (new BusinessValidator($password))
            ->minLength(BusinessValidator::PASSWORD_MIN_LENGTH)
            ->maxLength(BusinessValidator::PASSWORD_MAX_LENGTH)
            ->throwOnFail(new ControllerException(new ControllerException(I18n::tr("VALIDATOR_PASSWORD_LENGTH", [
                BusinessValidator::PASSWORD_MIN_LENGTH, BusinessValidator::PASSWORD_MAX_LENGTH
            ]))));

    }

}