<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 12:04
 */

namespace Framework\Exceptions\Entity;


use Framework\Exceptions\EntityException;
use Framework\Services\Locale\I18n;

class UserNotFoundException extends EntityException {
    function __construct() {
        parent::__construct(I18n::tr("ERROR_USER_NOT_FOUND_#"));
    }
}