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
use Framework\Preferences;
use Objects\User;

class UserValidator implements EntityValidator {

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
            ->length(Preferences::getSetting("validator", "user.login.min"),
                Preferences::getSetting("validator", "user.login.max"))
            ->throwOnFail(UserValidatorException::newIncorrectLoginLength())
            ->pattern(Preferences::getSetting("validator", "user.login.pattern"))
            ->throwOnFail(UserValidatorException::newIncorrectLoginChars())
            ->isLoginAvailable($this->user->getId())
            ->throwOnFail(UserValidatorException::newLoginUnavailable());
    }

    public function validateName() {
        (new BusinessValidator($this->user->getName()))
            ->maxLength(Preferences::getSetting("validator", "user.name.max"))
            ->throwOnFail(UserValidatorException::newIncorrectNameLength());
    }

    public function validateCountryId() {
        (new BusinessValidator($this->user->getCountryId()))
            ->isNullOrNumber()
            ->isCountryIdCorrect()
            ->throwOnFail(UserValidatorException::newIncorrectCountryId());
    }

    public function validateInfo() {
        (new Validator($this->user->getInfo()))
            ->maxLength(Preferences::getSetting("validator", "user.info.max"))
            ->throwOnFail(UserValidatorException::newInfoTooLong());
    }

    public function validatePermalink() {
        if (is_null($this->user->getPermalink()) || empty($this->user->getPermalink())) {
            return;
        }

        (new BusinessValidator($this->user->getPermalink()))
            ->permalink()
            ->throwOnFail(ValidatorException::newIncorrectPermalink())
            ->isPermalinkAvailableForUser($this->user->getId())
            ->throwOnFail(ValidatorException::newPermalinkIsUnavailable());
    }

    public function validateEmail() {
        (new BusinessValidator($this->user->getEmail()))
            ->isEmail()
            ->throwOnFail(UserValidatorException::newIncorrectEmail())
            ->isEmailAvailable($this->user->getId())
            ->throwOnFail(UserValidatorException::newUnavailableEmail());
    }

} 