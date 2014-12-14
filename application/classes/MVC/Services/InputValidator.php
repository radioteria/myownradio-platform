<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.12.14
 * Time: 9:35
 */

namespace MVC\Services;

use MVC\Exceptions\ControllerException;
use Tools\Optional;
use Tools\Singleton;

class InputValidator {

    use Singleton, Injectable;

    const PASSWORD_MIN_LENGTH = 3;
    const PASSWORD_MAX_LENGTH = 32;

    const EMAIL_REGEXP_PATTERN = "~^[\\w\\S]+@[\\w\\S]+\\.[\\w]{2,4}$~m";

    const LOGIN_MIN_LENGTH = 3;

    const STREAM_NAME_MIN_LENGTH = 3;

    /**
     * @param array $metadata
     * @return array
     * @throws ControllerException
     */
    public function trackMetadataValidator(array $metadata) {

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
            new ControllerException("Incorrect metadata"));

    }

    /**
     * @param string $password
     * @return string
     * @throws ControllerException
     */
    public function ValidatePassword($password) {

        $optional = new Optional($password, function ($password) {

            $len = strlen($password);
            return $len >= self::PASSWORD_MIN_LENGTH && $len <= self::PASSWORD_MAX_LENGTH;

        });

        return $optional->getOrElseThrow(
            new ControllerException("Password length must be between 3 and 32 chars"));

    }

    /**
     * @param string $email
     * @return string
     * @throws ControllerException
     */
    public function validateEmail($email) {

        $optional = new Optional($email, function ($email) {

            return preg_match(self::EMAIL_REGEXP_PATTERN, $email);

        });

        return $optional->getOrElseThrow(
            new ControllerException("Incorrect email format"));

    }

    /**
     * @param string $name
     * @return string
     * @throws ControllerException
     */
    public function validateStreamName($name) {

        $optional = new Optional($name, function ($name) {

            return strlen($name) >= self::STREAM_NAME_MIN_LENGTH;

        });

        return $optional->getOrElseThrow(
            new ControllerException("Stream name must contain at least 3 chars"));

    }

}