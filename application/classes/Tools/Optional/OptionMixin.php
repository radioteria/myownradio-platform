<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.06.2015
 * Time: 13:55
 */

namespace Tools\Optional;


trait OptionMixin {

    /*---------------------------------------------------------------*/
    /*                    Static Factory Methods                      */
    /*---------------------------------------------------------------*/

    /**
     * @param $value
     * @return Option
     */
    public static function ofNullable($value) {
        return is_null($value) ? None() : Some($value);
    }

    /**
     * @param $value
     * @param $predicate
     * @return Option
     */
    public static function of($value, $predicate) {
        return $predicate($value) ? Some($value) : None();
    }

    /**
     * @param $value
     * @return Option
     */
    public static function ofEmpty($value) {

        if (is_null($value)) return None();
        if (is_array($value) && count($value) == 0) return None();
        if (is_string($value) && strlen($value) == 0) return None();

        return Some($value);

    }

    /**
     * @param $value
     * @return Option
     */
    public static function ofNumber($value) {
        return is_numeric($value) ? Some($value) : None();
    }

    /**
     * @param $value
     * @return Option
     */
    public static function ofArray($value) {
        return is_array($value) ? Some($value) : None();
    }

    /**
     * @param $value
     * @return Option
     */
    public static function ofDeceptive($value) {
        return $value === false ? None() : Some($value);
    }

    /**
     * @param $filePath
     * @return Option
     */
    public static function ofFile($filePath) {
        return file_exists($filePath) ? Some($filePath) : None();
    }

    /**
     * @return None
     */
    public static function none() {
        return None();
    }

    /**
     * @param $value
     * @return Some
     */
    public static function some($value) {
        return Some($value);
    }

}