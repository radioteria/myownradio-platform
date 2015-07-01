<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 01.07.2015
 * Time: 14:54
 */

namespace Business\Validators;


use Framework\Preferences;
use Objects\User;
use Tools\Optional\StringFilter;

class LoginFilter {

    /**
     * @return \Closure
     */
    public static function validLength() {
        return StringFilter::length(
            Preferences::getSetting("validator", "user.login.min"),
            Preferences::getSetting("validator", "user.login.max")
        );
    }

    /**
     * @return \Closure
     */
    public static function validChars() {
        return StringFilter::match(
            Preferences::getSetting("validator", "user.login.pattern")
        );
    }

    /**
     * @return \Closure
     */
    public static function isAvailable() {

        return function ($value) {

            $preferences = Preferences::getInstance();

            if (in_array($value, $preferences->get("invalid", "login")->getOrElse([]))) {
                return false;
            }

            return User::getByFilter("login = :id OR mail = :id", array(":id" => $value))
                ->isEmpty();

        };

    }
}