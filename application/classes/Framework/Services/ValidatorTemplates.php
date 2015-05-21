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
use Objects\Stream;

trait ValidatorTemplates {

    /**
     * @param $email
     * @throws ControllerException
     */
    final public static function validateEmail($email) {

        (new BusinessValidator($email))
            ->email()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_EMAIL_FORMAT")))
            ->isEmailAvailable()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_EMAIL_UNAVAILABLE")));

    }

    /**
     * @param $login
     * @throws ControllerException
     */
    final public static function validateLogin($login) {

        (new BusinessValidator($login))
            ->login()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_LOGIN_CHARS")))
            ->minLength(BusinessValidator::LOGIN_MIN_LENGTH)
            ->maxLength(BusinessValidator::LOGIN_MAX_LENGTH)
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_LOGIN_LENGTH", [
                BusinessValidator::LOGIN_MIN_LENGTH, BusinessValidator::LOGIN_MAX_LENGTH
            ])))
            ->isLoginAvailable()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_LOGIN_UNAVAILABLE")));

    }

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


    /* Permalink Section */

    /**
     * @param $permalink
     * @param int|null $ignore_self
     * @throws ControllerException
     */
    final public static function validateStreamPermalink($permalink, $ignore_self = null) {

        if (is_null($permalink)) {
            return;
        }

        (new BusinessValidator($permalink))
            ->permalink()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_PERMALINK_CHARS")))
            ->isPermalinkAvailableForStream($ignore_self)
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_PERMALINK_USED")));

    }

    /**
     * @param $permalink
     * @param int|null $ignore_self
     * @throws ControllerException
     */
    final public static function validateUserPermalink($permalink, $ignore_self = null) {

        if (is_null($permalink)) {
            return;
        }

        (new BusinessValidator($permalink))
            ->permalink()
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_PERMALINK_CHARS")))
            ->isPermalinkAvailableForUser($ignore_self)
            ->throwOnFail(new ControllerException(I18n::tr("VALIDATOR_PERMALINK_USED")));

    }

    final public static function validateStreamObject(Stream $stream_object) {

        self::validateStreamPermalink($stream_object->getPermalink($stream_object->getID()));

        $validator->validateStreamCategory($category);
        $validator->validateStreamAccess($access);

    }

}