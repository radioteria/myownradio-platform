<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:24
 */

namespace Framework\Exceptions;


use Framework\Services\Locale\I18n;

/**
 * Class UnauthorizedException
 * @package Framework\Exceptions
 * @localized 21.05.2015
 */
class UnauthorizedException extends ControllerException {

    function __construct($message = null, $data = null) {
        parent::__construct($message, $data, 0);
    }

    static function noUserByLogin($id) {
        return new self(I18n::tr("ERROR_NO_USER_BY_LOGIN", ["login" => $id]));
    }

    public static function wrongLogin() {
        return new self(I18n::tr("ERROR_INCORRECT_LOGIN_OR_PASSWORD"));
    }

    public static function wrongPassword() {
        return new self(I18n::tr("ERROR_INCORRECT_PASSWORD"));
    }

    public static function noPermission() {
        return new self(I18n::tr("ERROR_NO_PERMISSION"));
    }

    public static function unAuthorized() {
        return new self(I18n::tr("ERROR_UNAUTHORIZED"));
    }

}