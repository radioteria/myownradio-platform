<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 16:59
 */

namespace Business\Validators\Exceptions\Etc;


use Business\Validators\Exceptions\ValidationException;
use Framework\Preferences;
use Framework\Services\Locale\I18n;

class UserNameLengthException extends ValidationException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_USER_NAME_LENGTH", [
            0,
            Preferences::getSetting("validator", "user.name.max")
        ]));
    }
}