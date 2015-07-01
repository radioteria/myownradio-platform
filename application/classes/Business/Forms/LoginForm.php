<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 13:59
 */

namespace Business\Forms;


use Business\Validators\Exceptions\Login\LoginCharsException;
use Business\Validators\Exceptions\Login\LoginLengthException;
use Business\Validators\Exceptions\Password\PasswordLengthException;
use Business\Validators\LoginFilter;
use Business\Validators\PasswordFilter;
use Framework\Injector\Injectable;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class LoginForm extends HttpForm implements SingletonInterface, Injectable {

    use Singleton;

    protected $login, $password;

    public function __construct() {
        parent::__construct();
    }

    /**
     * @return string
     */
    public function getLogin() {
        return $this->login;
    }

    /**
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * @throws \Exception
     */
    function validate() {

        Option::Some($this->login)
            ->filter(LoginFilter::validLength())
            ->orThrow(LoginLengthException::class)
            ->filter(LoginFilter::validChars())
            ->orThrow(LoginCharsException::class);

        Option::Some($this->password)
            ->filter(PasswordFilter::validLength())
            ->orThrow(PasswordLengthException::class);

    }

}