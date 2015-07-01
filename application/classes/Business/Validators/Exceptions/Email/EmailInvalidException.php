<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:46
 */

namespace Business\Validators\Exceptions\Email;


use Framework\Services\Locale\I18n;

class EmailInvalidException extends EmailException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_USER_EMAIL_FORMAT"));
    }
}