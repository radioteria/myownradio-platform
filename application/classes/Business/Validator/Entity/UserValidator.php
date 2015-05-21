<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 21.05.15
 * Time: 19:14
 */

namespace Business\Validator\Entity;


use Business\Validator\BusinessValidator;
use Business\Validator\Validator;
use Business\Validator\ValidatorException;
use Objects\User;

class UserValidator {

    const LOGIN_MIN_LENGTH = 3;
    const LOGIN_MAX_LENGTH = 32;
    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";
    const NAME_MAX_LENGTH = 32;
    const INFO_MAX_LENGTH = 4096;

    public static function validate(User $user) {
        self::validateLogin($user->getLogin(), $user->getID());
        self::validateName($user->getName());
        self::validateCountryId($user->getCountryId());
        self::validateInfo($user->getInfo());
        self::validatePermalink($user->getPermalink(), $user->getID());
        self::validateEmail($user->getEmail(), $user->getID());
    }

    private static function validateLogin($login, $ignore = null) {
        (new BusinessValidator($login))
            ->isInRange(self::LOGIN_MIN_LENGTH, self::LOGIN_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newIncorrectLoginLength())
            ->pattern(self::LOGIN_PATTERN)
            ->throwOnFail(UserValidatorException::newIncorrectLoginChars())
            ->isLoginAvailable($ignore)
            ->throwOnFail(UserValidatorException::newLoginUnavailable());
    }

    private static function validateName($name) {
        (new BusinessValidator($name))
            ->maxLength(self::NAME_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newIncorrectNameLength());
    }

    private static function validateCountryId($countryId) {
        (new BusinessValidator($countryId))
            ->isNumber()
            ->isCountryIdCorrect()
            ->throwOnFail(UserValidatorException::newIncorrectCountryId());
    }

    private static function validateInfo($info) {
        (new Validator($info))
            ->maxLength(self::INFO_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newInfoTooLong());
    }

    private static function validatePermalink($permalink, $ignoreSelf) {
        if (is_null($permalink)) {
            return;
        }

        (new BusinessValidator($permalink))
            ->permalink()
            ->throwOnFail(ValidatorException::newIncorrectPermalink())
            ->isPermalinkAvailableForUser($ignoreSelf)
            ->throwOnFail(ValidatorException::newPermalinkIsUnavailable());
    }

    private static function validateEmail($email, $ignoredId) {
        (new BusinessValidator($email))
            ->email()
            ->throwOnFail(UserValidatorException::newIncorrectEmail())
            ->isEmailAvailable($ignoredId)
            ->throwOnFail(UserValidatorException::newUnavailableEmail());
    }

} 