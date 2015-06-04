<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.06.15
 * Time: 16:31
 */

namespace Framework\Exceptions\Auth;


use Framework\Exceptions\AccessException;
use Framework\Services\Locale\I18n;

class NoUserByLoginException extends AccessException {
    public function __construct($login) {
        parent::__construct(I18n::tr("ERROR_NO_USER_BY_LOGIN", [ $login ]));
    }
} 