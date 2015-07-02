<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 9:26
 */

namespace Business\Validators;


use Framework\Preferences;
use Objects\Stream;
use Objects\User;
use Tools\Optional\Filter;
use Tools\Optional\StringFilter;

class PermalinkFilter {

    /**
     * Checks whether $permalink length is valid.
     * @return \Closure
     */
    public static function validLength() {
        return Filter::matchAny(
            StringFilter::lengthIs(0),
            StringFilter::maxLength(Preferences::getSetting("validator", "permalink.max"))
        );
    }

    /**
     * Checks characters used in $permalink.
     * @return \Closure
     */
    public static function validChars() {
        return StringFilter::match(Preferences::getSetting("validator", "permalink.pattern"));
    }

    /**
     * Checks database to avoid $permalink collisions.
     * @return \Closure
     */
    public static function isAvailableForUser() {
        return function ($permalink) {
            return empty($permalink) ? true : User::getByFilter("permalink = ?", array($permalink))->isEmpty();
        };
    }

    /**
     * Checks database to avoid $permalink collisions.
     * @return \Closure
     */
    public static function isAvailableForStream() {
        return function ($permalink) {
            return Stream::getByFilter("permalink = ?", array($permalink))->isEmpty();
        };
    }

}