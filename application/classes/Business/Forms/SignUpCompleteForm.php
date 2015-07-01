<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 16:05
 */

namespace Business\Forms;


use Business\Validators\Exceptions\Code\CodeNotActualException;
use Business\Validators\Exceptions\Code\CodeParsingException;
use Business\Validators\Exceptions\Etc\UserNameLengthException;
use Business\Validators\Exceptions\Login\LoginCharsException;
use Business\Validators\Exceptions\Login\LoginLengthException;
use Business\Validators\Exceptions\Login\LoginUnavailableException;
use Business\Validators\Exceptions\Password\PasswordLengthException;
use Business\Validators\LoginFilter;
use Business\Validators\PasswordFilter;
use Framework\Injector\Injectable;
use Framework\Preferences;
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
     * @return bool
     * @throws CodeNotActualException
     * @throws CodeParsingException
     */
    public function extractEmail() {

        $array = self::parseCode($this->code);

        if (!isset($array["email"], $array["code"])) {
            throw new CodeParsingException;
        }

        if ($array["code"] !== md5($array["email"] . "@myownradio.biz@" . $array["email"])) {
            throw new CodeNotActualException;
        }

        $this->_email = $array["email"];

    }

    public function validate() {

        $this->extractEmail();

        Option::Some($this->login)
            ->filter(LoginFilter::validLength())
            ->orThrow(LoginLengthException::class)
            ->filter(LoginFilter::validChars())
            ->orThrow(LoginCharsException::class)
            ->filter(LoginFilter::isAvailable())
            ->orThrow(LoginUnavailableException::class);

        Option::Some($this->password)
            ->filter(PasswordFilter::validLength())
            ->orThrow(PasswordLengthException::class);

        Option::Some($this->name)
            ->filter(StringFilter::maxLength(
                Preferences::getSetting("validator", "user.name.max")))
            ->orThrow(UserNameLengthException::class);

        Option::Some($this->permalink);

        Option::Some($this->country_id);

        Option::Some($this->info);

        Option::Some($this->_email);

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