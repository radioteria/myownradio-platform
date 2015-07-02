<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:28
 */

namespace Business\Validators\Exceptions\Etc;


use Business\Validators\Exceptions\ValidationException;
use Framework\Preferences;
use Framework\Services\Locale\I18n;

class UserInfoLengthException extends ValidationException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_USER_INFO_LENGTH", array(
            0, Preferences::getSetting("validator", "user.info.max")
        )));
    }
}