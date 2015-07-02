<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 16:05
 */

namespace Business\Forms;


use Business\Validators\CountryFilter;
use Business\Validators\EmailFilter;
use Business\Validators\Exceptions\Code;
use Business\Validators\Exceptions\Email\EmailInvalidException;
use Business\Validators\Exceptions\Email\EmailUnavailableException;
use Business\Validators\Exceptions\Etc;
use Business\Validators\Exceptions\Etc\UserInfoLengthException;
use Business\Validators\Exceptions\Login;
use Business\Validators\Exceptions\Password;
use Business\Validators\Exceptions\Permalink;
use Business\Validators\LoginFilter;
use Business\Validators\PasswordFilter;
use Business\Validators\PermalinkFilter;
use Framework\Injector\Injectable;
use Framework\Preferences;
use Tools\Optional\Mapper;
use Tools\Optional\Option;
use Tools\Optional\StringFilter;
use Tools\Singleton;
use Tools\SingletonInterface;

class SignUpCompleteForm extends HttpForm implements SingletonInterface, Injectable {

    use Singleton;

    protected $code, $login, $password, $name, $info, $permalink, $country_id, $_email;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @throws Code\CodeNotActualException
     * @throws Code\CodeParsingException
     */
    public function extractEmail() {

        $array = self::parseCode($this->code);

        if (!isset($array["email"], $array["code"])) {
            throw new Code\CodeParsingException;
        }

        if ($array["code"] !== md5($array["email"] . "@myownradio.biz@" . $array["email"])) {
            throw new Code\CodeNotActualException;
        }

        $this->_email = $array["email"];

    }

    public function validate() {

        $this->extractEmail();

        Option::Some($this->login)
            ->filter(LoginFilter::validLength())
            ->orThrow(Login\LoginLengthException::class)
            ->filter(LoginFilter::validChars())
            ->orThrow(Login\LoginCharsException::class)
            ->filter(LoginFilter::isAvailable())
            ->orThrow(Login\LoginUnavailableException::class);

        Option::Some($this->password)
            ->filter(PasswordFilter::validLength())
            ->orThrow(Password\PasswordLengthException::class);

        Option::Some($this->name)
            ->filter(StringFilter::maxLength(
                Preferences::getSetting("validator", "user.name.max")))
            ->orThrow(Etc\UserNameLengthException::class);

        Option::Some($this->permalink)
            ->filter(PermalinkFilter::validLength())
            ->orThrow(Permalink\PermalinkLengthException::class)
            ->filter(PermalinkFilter::validChars())
            ->orThrow(Permalink\PermalinkCharsException::class)
            ->filter(PermalinkFilter::isAvailableForUser())
            ->orThrow(Permalink\PermalinkUnavailableException::class);

        Option::Some($this->country_id)
            ->map(Mapper::emptyToNull())
            ->filter(CountryFilter::validCountryId())
            ->orThrow(Etc\BadCountryException::class);

        Option::Some($this->info)
            ->filter(StringFilter::maxLength(
                Preferences::getSetting("validator", "user.info.max")))
            ->orThrow(UserInfoLengthException::class);

        Option::Some($this->_email)
            ->filter(EmailFilter::isValid())
            ->orThrow(EmailInvalidException::class)
            ->filter(EmailFilter::isAvailable())
            ->orThrow(EmailUnavailableException::class);

    }

    /**
     * @return mixed
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @return mixed
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getPermalink() {
        return $this->permalink;
    }

    /**
     * @return mixed
     */
    public function getCountryId() {
        return $this->country_id;
    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->_email;
    }


}