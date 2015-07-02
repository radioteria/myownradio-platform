<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:15
 */

namespace Business\Validators\Exceptions\Permalink;


use Framework\Preferences;
use Framework\Services\Locale\I18n;

class PermalinkLengthException extends PermalinkException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_PERMALINK_LENGTH", array(
            Preferences::getSetting("validator", "permalink.min"),
            Preferences::getSetting("validator", "permalink.max")
        )));
    }
}