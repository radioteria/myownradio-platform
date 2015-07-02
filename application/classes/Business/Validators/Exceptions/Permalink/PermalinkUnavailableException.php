<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:21
 */

namespace Business\Validators\Exceptions\Permalink;


use Framework\Services\Locale\I18n;

class PermalinkUnavailableException extends PermalinkException {
    public function __construct() {
        parent::__construct(I18n::tr("VALIDATOR_PERMALINK_UNAVAILABLE"));
    }
}