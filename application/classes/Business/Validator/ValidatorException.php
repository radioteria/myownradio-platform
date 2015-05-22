<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 21.05.15
 * Time: 16:08
 */

namespace Business\Validator;


use Framework\Exceptions\ControllerException;

/**
 * Class ValidatorException
 * @package Business\Validator
 */
class ValidatorException extends ControllerException {
    function __construct($message = null, $data = null, $status = 0) {
        parent::__construct($message, $data, $status);
    }

    public static function newIncorrectCountryId() {
        return self::tr("VALIDATOR_COUNTRY_ID");
    }

    public static function newPermalinkIsUnavailable() {
        return self::tr("VALIDATOR_PERMALINK_UNAVAILABLE");
    }

    public static function newIncorrectPermalink() {
        return self::tr("VALIDATOR_PERMALINK_CHARS");
    }
}