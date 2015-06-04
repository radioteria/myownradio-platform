<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.06.15
 * Time: 16:42
 */

namespace Framework\Exceptions\Auth;


use Framework\Services\Locale\I18n;

class UnauthorizedException extends \Framework\Exceptions\AccessException {
    public function __construct() {
        parent::__construct(I18n::tr("ERROR_UNAUTHORIZED"));
    }
} 