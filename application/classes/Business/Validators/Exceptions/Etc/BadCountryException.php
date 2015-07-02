<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:22
 */

namespace Business\Validators\Exceptions\Etc;


use Business\Validators\Exceptions\ValidationException;
use Framework\Services\Locale\I18n;

class BadCountryException extends ValidationException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_COUNTRY_ID"));
    }
}