<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.12.14
 * Time: 9:35
 */

class Validators {

    const PASSWORD_MIN_LENGTH = 3;
    const PASSWORD_MAX_LENGTH = 32;

    const EMAIL_REGEXP_PATTERN = "~^[\\w\\S]+@[\\w\\S]+\\.[\\w]{2,4}$~m";

    const LOGIN_MIN_LENGTH = 3;

    const STREAM_NAME_MIN_LENGTH = 3;

    /**
     * @param array $metadata
     * @return array
     * @throws validException
     */
    public static function trackMetadataValidator(array $metadata) {

        $optional = new Optional($metadata, function ($variable) {

            $reqKeys = array("artist", "title", "album", "track_number", "genre", "date");

            foreach($reqKeys as $key) {
                if (array_key_exists($key, $variable) === false) {
                    return false;
                }
            }

            return true;

        });

        return $optional->getOrElseThrow(
            new validException("Incorrect metadata"));

    }

    /**
     * @param string $password
     * @return string
     * @throws validException
     */
    public static function userPasswordValidator($password) {

        $optional = new Optional($password, function ($password) {

            if (strlen($password) < self::PASSWORD_MIN_LENGTH) return false;
            if (strlen($password) > self::PASSWORD_MAX_LENGTH) return false;

            return true;

        });

        return $optional->getOrElseThrow(
            new validException("Password length must be between 3 and 32 chars"));

    }

    /**
     * @param string $email
     * @return string
     * @throws validException
     */
    public static function emailValidator($email) {

        $optional = new Optional($email, function ($email) {

            return preg_match(self::EMAIL_REGEXP_PATTERN, $email);

        });

        return $optional->getOrElseThrow(
            new validException("Incorrect email format"));

    }

    /**
     * @param string $name
     * @return string
     * @throws validException
     */
    public static function streamNameValidator($name) {

        $optional = new Optional($name, function ($name) {

            return strlen($name) >= self::STREAM_NAME_MIN_LENGTH;

        });

        return $optional->getOrElseThrow(
            new validException("Stream name must contain at least 3 chars"));

    }

} 