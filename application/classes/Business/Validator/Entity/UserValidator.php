<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 21.05.15
 * Time: 19:14
 */

namespace Business\Validator\Entity;


use Business\Validator\BusinessValidator;
use Objects\User;

class UserValidator {

    const LOGIN_MIN_LENGTH = 3;
    const LOGIN_MAX_LENGTH = 32;
    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";
    const NAME_MAX_LENGTH = 32;

    public static function validate(User $stream) {
        self::validateLogin($stream->getLogin(), $stream->getID());
        self::validateName($stream->getName());
        self::validateCountryId($stream->getCountryId());
        self::validateInfo($stream->getInfo());
        self::validatePermalink($stream->getPermalink());
    }

    private static function validateLogin($login, $ignore = null) {
        (new BusinessValidator($login))
            ->inRange(self::LOGIN_MIN_LENGTH, self::LOGIN_MAX_LENGTH)
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

    private static function validateCountryId($getCountryId) {
    }

    private static function validateInfo($getInfo) {
    }

    private static function validatePermalink($getPermalink) {
    }

} 