<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.07.2015
 * Time: 10:06
 */

namespace Business\Validators;


use Objects\Country;

class CountryFilter {

    /**
     * @return \Closure
     */
    public static function validCountryId() {
        return function ($value) {
            return is_null($value) ? true : Country::getByID($value)->nonEmpty();
        };
    }

}