<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
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

    const PERMALINK_REGEXP_PATTERN = "~(^[a-z0-9\\-]*$)~m";
    const TRACKS_LIST_PATTERN = "~^[0-9]+(,[0-9]+)*$~m";

    const LOGIN_MIN_LENGTH = 3;
    const LOGIN_PATTERN = "~^[0-9a-z_]+$~m";

    const STREAM_NAME_MIN_LENGTH = 3;

    /**
     * @param array $metadata
     * @return array
     * @throws ControllerException
     */
    public function validateTrackMetadata(array $metadata) {

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
    public function validatePassword($password) {

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
            new ControllerException("Stream name must contain at least 3 chars")
        );

    }

    public function validateLogin($login) {

        $optional = new Optional($login, function ($login) {

            if (strlen($login) < self::LOGIN_MIN_LENGTH) {
                return false;
            }

            return preg_match(self::LOGIN_PATTERN, $login);

        });

        return $optional->getOrElseThrow(
            new ControllerException("Login must contain only 'a-z, 0-9, _' and be at least 3 chars long")
        );

    }

    /**
     * @param $permalink
     * @param bool|int $selfCheck
     * @return mixed
     * @throws ControllerException
     */
    public function validateStreamPermalink($permalink, $selfCheck = false) {

        $optional = new Optional($permalink, function ($permalink) use ($selfCheck) {

            // Permalink can be NULL. It means that stream has no permalink.
            if ($permalink === null) {
                return true;
            }

            // Permalink must be a string
            if (!is_string($permalink)) {
                return false;
            }

            // Permalink could not be an empty string
            if (strlen($permalink) == 0) {
                return false;
            }

            // Permalink must match pattern
            if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
                return false;
            }

            // Permalink must be unique
            if ($selfCheck === false) {
                $test = Database::getInstance()->fetchOneColumn("SELECT COUNT(*) FROM r_streams WHERE permalink = ?",
                    [$permalink])->getOrElseThrow(ControllerException::databaseError());
            } else {
                $test = Database::getInstance()->fetchOneColumn("SELECT COUNT(*) FROM r_streams WHERE permalink = ? AND sid != ?",
                    [$permalink, $selfCheck])->getOrElseThrow(ControllerException::databaseError());
            }

            return !boolval($test);

        });

        return $optional->getOrElseThrow(
            new ControllerException(sprintf("'%s' is not valid stream permalink", $permalink))
        );

    }

    /**
     * @param $email
     * @return mixed
     */
    public function validateUniqueUserEmail($email) {

        $optional = new Optional($email, function ($email) {

            return !boolval(Database::getInstance()->fetchOneColumn(
                "SELECT COUNT(*) FROM r_users WHERE mail = ?", [$email])->get());

        });

        return $optional->getOrElseThrow(
            new ControllerException(sprintf("UserModel with email '%s' already exists", $email))
        );

    }

    /**
     * @param $tracks
     * @return mixed
     */
    public function validateTracksList($tracks) {

        $optional = new Optional($tracks, function ($tracks) {

            return preg_match(self::TRACKS_LIST_PATTERN, $tracks);

        });

        return $optional->getOrElseThrow(new ControllerException("Invalid tracks list", $tracks));

    }

    /**
     * @param $code
     * @return mixed
     */
    public function validateRegistrationCode($code) {

        $optional = new Optional($code, function ($code) {

            $json = base64_decode($code);

            if ($json === false) {
                return false;
            }

            $decoded = json_decode($json, true);

            if (is_null($decoded) || empty($decoded["email"]) || empty($decoded["code"])) {
                return false;
            }

            return md5($decoded['email'] . "@myownradio.biz@" . $decoded['email']) === $decoded['code'];

        });

        return $optional->getOrElseThrow(new ControllerException("Incorrect code"));

    }

    public function validateUserPermalink($permalink, $selfCheck = false) {

        $optional = new Optional($permalink, function ($permalink) use ($selfCheck) {

            if ($permalink === null) {
                return true;
            }

            if (!is_string($permalink)) {
                return false;
            }

            if (strlen($permalink) == 0) {
                return false;
            }

            if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
                return false;
            }

            if ($selfCheck === false) {
                $test = Database::getInstance()->fetchOneColumn("SELECT COUNT(*) FROM r_users WHERE permalink = ?",
                    [$permalink])->getOrElseThrow(ControllerException::databaseError());
            } else {
                $test = Database::getInstance()->fetchOneColumn("SELECT COUNT(*) FROM r_users WHERE permalink = ? AND uid != ?",
                    [$permalink, $selfCheck])->getOrElseThrow(ControllerException::databaseError());
            }

            return !boolval($test);

        });

        return $optional->getOrElseThrow(
            new ControllerException(sprintf("'%s' is not valid stream permalink", $permalink))
        );

    }

}