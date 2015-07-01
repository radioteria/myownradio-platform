<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:40
 */

namespace Business\Forms;


use Business\Validators\EmailFilter;
use Business\Validators\Exceptions\Email\EmailInvalidException;
use Business\Validators\Exceptions\Email\EmailUnavailableException;
use Framework\Injector\Injectable;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;

class SignUpStartForm extends HttpForm implements SingletonInterface, Injectable {

    use Singleton;

    protected $email;

    public function __construct() {
        parent::__construct();
    }

    public function validate() {

        Option::Some($this->email)
            ->filter(EmailFilter::isValid())
            ->orThrow(EmailInvalidException::class)
            ->filter(EmailFilter::isAvailable())
            ->orThrow(EmailUnavailableException::class);

    }

    /**
     * @return mixed
     */
    public function getEmail() {
        return $this->email;
    }

}