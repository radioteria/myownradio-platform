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

class UserValidator implements EntityValidator {

    const LOGIN_MIN_LENGTH = 3;
    const LOGIN_MAX_LENGTH = 32;
    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";
    const NAME_MAX_LENGTH = 32;
    const INFO_MAX_LENGTH = 4096;

    /** @var User */
    private $user;

    /**
     * @param User $user
     * @throws ValidatorException
     */
    public static function validate(User $user) {
        $validator = new self($user);
        $validator->validateAllFields();
    }

    public function __construct(User $user) {
        $this->user = $user;
    }

    public function validateAllFields() {
        $this->validateLogin();
        $this->validateName();
        $this->validateCountryId();
        $this->validateInfo();
        $this->validatePermalink();
        $this->validateEmail();
    }

    public function validateLogin() {
        (new BusinessValidator($this->user->getLogin()))
            ->isInRange(self::LOGIN_MIN_LENGTH, self::LOGIN_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newIncorrectLoginLength())
            ->pattern(self::LOGIN_PATTERN)
            ->throwOnFail(UserValidatorException::newIncorrectLoginChars())
            ->isLoginAvailable($this->user->getID())
            ->throwOnFail(UserValidatorException::newLoginUnavailable());
    }

    public function validateName() {
        (new BusinessValidator($this->user->getName()))
            ->maxLength(self::NAME_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newIncorrectNameLength());
    }

    public function validateCountryId() {
        (new BusinessValidator($this->user->getCountryId()))
            ->isNumber()
            ->isCountryIdCorrect()
            ->throwOnFail(UserValidatorException::newIncorrectCountryId());
    }

    public function validateInfo() {
        (new Validator($this->user->getInfo()))
            ->maxLength(self::INFO_MAX_LENGTH)
            ->throwOnFail(UserValidatorException::newInfoTooLong());
    }

    public function validatePermalink() {
        if (is_null($this->user->getPermalink())) {
            return;
        }

        (new BusinessValidator($this->user->getPermalink()))
            ->permalink()
            ->throwOnFail(ValidatorException::newIncorrectPermalink())
            ->isPermalinkAvailableForUser($this->user->getID())
            ->throwOnFail(ValidatorException::newPermalinkIsUnavailable());
    }

    public function validateEmail() {
        (new BusinessValidator($this->user->getEmail()))
            ->email()
            ->throwOnFail(UserValidatorException::newIncorrectEmail())
            ->isEmailAvailable($this->user->getID())
            ->throwOnFail(UserValidatorException::newUnavailableEmail());
    }

} 