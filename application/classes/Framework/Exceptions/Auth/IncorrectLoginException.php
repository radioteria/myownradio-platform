<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.06.15
 * Time: 16:36
 */

namespace Framework\Exceptions\Auth;


use Framework\Exceptions\AccessException;
use Framework\Services\Locale\I18n;

class IncorrectLoginException extends AccessException {
    public function __construct() {
        parent::__construct(I18n::tr("ERROR_INCORRECT_LOGIN_OR_PASSWORD"));
    }
} 