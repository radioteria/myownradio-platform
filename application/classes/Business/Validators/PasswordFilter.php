<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:11
 */

namespace Business\Validators;


use Framework\Preferences;
use Tools\Optional\StringFilter;

class PasswordFilter {

    /**
     * @return \Closure
     */
    public static function validLength() {
        return StringFilter::length(
            Preferences::getSetting("validator", "user.password.min"),
            Preferences::getSetting("validator", "user.password.max")
        );
    }

}