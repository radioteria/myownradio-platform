<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 15:44
 */

namespace Business\Validators;


use Framework\Preferences;
use Objects\User;
use Tools\Optional\StringFilter;

class EmailFilter {

    /**
     * @return \Closure
     */
    public static function isValid() {
        return StringFilter::match(
            Preferences::getSetting("validator", "email.pattern")
        );
    }

    /**
     * @return \Closure
     */
    public static function isAvailable() {
        return function ($email) {
            return User::getByFilter("FIND_BY_EMAIL", array($email))->isEmpty();
        };
    }


}