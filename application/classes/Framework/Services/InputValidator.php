<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 12.12.14
 * Time: 9:35
 */

namespace Framework\Services;

use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\DB\DBQuery;
use Objects\Category;
use Objects\Color;
use Objects\Country;
use Tools\Singleton;

class InputValidator implements Injectable {

    use Singleton;

    const PASSWORD_MIN_LENGTH = 6;
    const PASSWORD_MAX_LENGTH = 32;

    const EMAIL_REGEXP_PATTERN = "~^[\\w\\S]+@[\\w\\S]+\\.[\\w]{2,4}$~";

    const PERMALINK_REGEXP_PATTERN = "~(^[a-z0-9\\-]*$)~";
    const TRACKS_LIST_PATTERN = "~^[0-9]+(,[0-9]+)*$~";

    const LOGIN_MIN_LENGTH = 3;
    const LOGIN_MAX_LENGTH = 32;
    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";

    const STREAM_NAME_MIN_LENGTH = 3;


    /**
     * @param $countryID
     * @throws ControllerException
     */
    public function validateCountryID($countryID) {
        if (is_null($countryID)) return;

        Country::getByID($countryID)
            ->getOrElseThrow(new ControllerException(sprintf("Country with id %d does not exist", $countryID)));
    }

    /**
     * @param array $metadata
     * @throws ControllerException
     */
    public function validateTrackMetadata($metadata) {

        $reqKeys = array("artist", "title", "album", "track_number", "genre", "date");

        foreach ($metadata as $key) {
            if (array_key_exists($key, $reqKeys) === false) {
                throw new ControllerException("Incorrect metadata");
            }
        }

    }

    /**
     * @param string $password
     * @throws ControllerException
     */
    public function validatePassword($password) {

        $len = strlen($password);
        if ($len < self::PASSWORD_MIN_LENGTH && $len > self::PASSWORD_MAX_LENGTH) {
            throw new ControllerException("Password length must be between 3 and 32 chars");
        }

    }

    /**
     * @param string $email
     * @throws ControllerException
     */
    public function validateEmail($email) {

        if (!preg_match(self::EMAIL_REGEXP_PATTERN, $email)) {
            throw new ControllerException("Incorrect email format");
        }

    }

    /**
     * @param string $name
     * @throws ControllerException
     */
    public function validateStreamName($name) {

        if (strlen($name) < self::STREAM_NAME_MIN_LENGTH) {
            throw new ControllerException("Stream name must contain at least 3 chars");
        }

        $name_lower = mb_strtolower($name, "utf8");
        foreach (Defaults::getStopWords() as $word) {
            if (mb_strpos($name_lower, $word, 0, "utf8") !== FALSE) {
                throw new ControllerException("Stream name contains words that can not be used");
            }
        }

    }

    public function validateLogin($login) {

        if (strlen($login) < self::LOGIN_MIN_LENGTH || strlen($login) > self::LOGIN_MAX_LENGTH) {
            throw new ControllerException(sprintf("Login must be in range from %d to %d chars",
                self::LOGIN_MIN_LENGTH, self::LOGIN_MAX_LENGTH));
        }

        if (!preg_match(self::LOGIN_PATTERN, $login)) {
            throw new ControllerException("Login must contain only [a-z, 0-9 or \"_\"] chars");
        }

    }

    /**
     * @param $permalink
     * @param bool|int $selfIgnore
     * @throws ControllerException
     */
    public function validateStreamPermalink($permalink, $selfIgnore = null) {

        $dbq = DBQuery::getInstance();

        if (!is_null($permalink) && !is_string($permalink)) {
            throw new ControllerException("Valid permalink is [null|string]");
        }

        if (is_null($permalink)) {
            return;
        }

        if (strlen($permalink) == 0) {
            throw new ControllerException("Permalink couldn't be an empty string");
        }

        if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
            throw new ControllerException("Permalink must contain only [a-z, 0-9 or \"-\"] chars");
        }

        $query = $dbq->selectFrom("r_streams")->where("(permalink = :key OR sid = :key)", [":key" => $permalink]);

        if (is_numeric($selfIgnore)) {
            $query->where("sid != ?", [$selfIgnore]);
        }

        if(count($query) > 0) {
            throw new ControllerException("Permalink is used by another stream");
        }

    }


    public function validateUserPermalink($permalink, $selfIgnore = null) {

        $dbq = DBQuery::getInstance();

        if (!is_null($permalink) && !is_string($permalink)) {
            throw new ControllerException("Valid permalink is [null|string]");
        }

        if (is_null($permalink)) {
            return;
        }

        if (strlen($permalink) == 0) {
            throw new ControllerException("Permalink couldn't be an empty string");
        }

        if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
            throw new ControllerException("Permalink must contain only [a-z, 0-9 or \"-\"] chars");
        }

        $query = $dbq->selectFrom("r_users")->where("(permalink = :key OR uid = :key)", [":key" => $permalink]);

        if (is_numeric($selfIgnore)) {
            $query->where("uid != ?", [$selfIgnore]);
        }

        if(count($query) > 0) {
            throw new ControllerException("Permalink is used by another user");
        }

    }

    /**
     * @param $email
     * @throws ControllerException
     */
    public function validateUniqueUserEmail($email) {

        $dbq = DBQuery::getInstance();

        $query = $dbq->selectFrom("r_users")->where("mail", $email);

        if (count($query) > 0) {
            throw new ControllerException(sprintf("User with email '%s' already exists", $email));
        }

    }

    /**
     * @param $tracks
     * @throws ControllerException
     */
    public function validateTracksList($tracks) {

        if (!preg_match(self::TRACKS_LIST_PATTERN, $tracks)) {
            throw new ControllerException("Invalid tracklist", $tracks);
        }

    }

    /**
     * @param $code
     * @throws ControllerException
     */
    public function validateRegistrationCode($code) {

        $decoded    = base64_decode($code);

        if ($decoded === false) {
            throw new ControllerException("Code is not valid");
        }

        $object     = json_decode($decoded, true);

        if (is_null($object) || empty($object["email"]) || empty($object["code"])) {
            throw new ControllerException("Code is not valid");
        }

        if (md5($object['email'] . "@myownradio.biz@" . $object['email']) !== $object['code']) {
            throw new ControllerException("Code does not match the email");
        }

    }

    /**
     * @param $category
     * @throws ControllerException
     */
    public function validateStreamCategory($category) {

        Category::getByID($category)
            ->justThrow(new ControllerException(sprintf("Invalid stream category specified", $category)));

    }

    /**
     * @param $access
     * @throws ControllerException
     */
    public function validateStreamAccess($access) {

        if (array_search($access, ['PUBLIC', 'UNLISTED', 'PRIVATE']) === false) {
            throw new ControllerException(sprintf("'%s' is not valid stream access mode", $access));
        }

    }

    /**
     * @param $file
     * @throws ControllerException
     */
    public function validateImageMIME($file) {
        $fileInfo = new \finfo();
        $mime = $fileInfo->file($file, FILEINFO_MIME);
        if (strpos($mime, "image", 0) !== 0) {
            throw new ControllerException("File is not valid image file");
        }
    }

    public function validateTrackColor($color) {
        Color::getByID($color)
            ->justThrow(new ControllerException(sprintf("Invalid color id specified", $color)));
    }

}