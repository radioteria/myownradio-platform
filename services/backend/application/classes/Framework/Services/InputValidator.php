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
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Locale\I18n;
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

    const USER_NAME_MAX_LENGTH = 32;

    const LOGIN_PATTERN = "~^[0-9a-z\\_]+$~";

    const STREAM_NAME_MIN_LENGTH = 3;
    const STREAM_NAME_MAX_LENGTH = 32;


    /**
     * @param $countryID
     * @throws ControllerException
     */
    public function validateCountryID($countryID) {
        if (is_null($countryID)) return;

        Country::getByID($countryID)
            ->getOrElseThrow(new ControllerException(I18n::tr("VALIDATOR_NO_COUNTRY", [$countryID])));
    }

    /**
     * @param string $password
     * @throws ControllerException
     */
    public function validatePassword($password) {

        $len = strlen($password);
        if ($len < self::PASSWORD_MIN_LENGTH && $len > self::PASSWORD_MAX_LENGTH) {
            throw new ControllerException(I18n::tr("VALIDATOR_PASSWORD_LENGTH", [
                self::PASSWORD_MIN_LENGTH, self::PASSWORD_MAX_LENGTH
            ]));
        }

    }

    /**
     * @param string $email
     * @throws ControllerException
     */
    public function validateEmail($email) {

        if (!preg_match(self::EMAIL_REGEXP_PATTERN, $email)) {
            throw new ControllerException(I18n::tr("VALIDATOR_EMAIL_FORMAT"));
        }

        if (count((new SelectQuery("r_users"))->where("mail", [$email]))) {
            throw new ControllerException(I18n::tr("VALIDATOR_EMAIL_UNAVAILABLE"));
        }

    }

    /**
     * @param string $name
     * @throws ControllerException
     */
    public function validateStreamName($name) {

        if (strlen($name) < self::STREAM_NAME_MIN_LENGTH) {
            throw new ControllerException(I18n::tr("VALIDATOR_STREAM_NAME_SHORT"));
        }

        $name_lower = mb_strtolower($name, "utf8");
        foreach (Defaults::getStopWords() as $word) {
            if (mb_strpos($name_lower, $word, 0, "utf8") !== FALSE) {
                throw new ControllerException(I18n::tr("VALIDATOR_STREAM_WORDS"));
            }
        }

    }


    public function validateUserName($name) {

        if (strlen($name) > self::USER_NAME_MAX_LENGTH) {
            throw new ControllerException(I18n::tr("VALIDATOR_USER_NAME_LENGTH", [
                self::USER_NAME_MAX_LENGTH
            ]));
        }

    }

    public function validateLogin($login) {

        if (strlen($login) < self::LOGIN_MIN_LENGTH || strlen($login) > self::LOGIN_MAX_LENGTH) {
            throw new ControllerException(I18n::tr("VALIDATOR_LOGIN_LENGTH", [
                self::LOGIN_MIN_LENGTH, self::LOGIN_MAX_LENGTH
            ]));
        }

        if (!preg_match(self::LOGIN_PATTERN, $login)) {
            throw new ControllerException(I18n::tr("VALIDATOR_LOGIN_CHARS"));
        }

        if (count((new SelectQuery("r_users"))->where("login", $login))) {
            throw new ControllerException(I18n::tr("VALIDATOR_LOGIN_UNAVAILABLE"));
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
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_FORMAT"));
        }

        if (is_null($permalink)) {
            return;
        }

        if (strlen($permalink) == 0) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_EMPTY"));
        }

        if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_CHARS"));
        }

        $query = $dbq->selectFrom("r_streams")->where("(permalink = :key OR sid = :key)", [":key" => $permalink]);

        if (is_numeric($selfIgnore)) {
            $query->where("sid != ?", [$selfIgnore]);
        }

        if(count($query) > 0) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_USED"));
        }

    }


    public function validateUserPermalink($permalink, $selfIgnore = null) {

        $dbq = DBQuery::getInstance();

        if (!is_null($permalink) && !is_string($permalink)) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_FORMAT"));
        }

        if (is_null($permalink)) {
            return;
        }

        if (strlen($permalink) == 0) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_EMPTY"));
        }

        if (!preg_match(self::PERMALINK_REGEXP_PATTERN, $permalink)) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_CHARS"));
        }

        $query = $dbq->selectFrom("r_users")->where("(permalink = :key OR uid = :key)", [":key" => $permalink]);

        if (is_numeric($selfIgnore)) {
            $query->where("uid != ?", [$selfIgnore]);
        }

        if(count($query) > 0) {
            throw new ControllerException(I18n::tr("VALIDATOR_PERMALINK_USED"));
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
            throw new ControllerException(I18n::tr("VALIDATOR_EMAIL_EXISTS", [$email]));
        }

    }

    /**
     * @param $tracks
     * @throws ControllerException
     */
    public function validateTracksList($tracks) {

        if (!preg_match(self::TRACKS_LIST_PATTERN, $tracks)) {
            throw new ControllerException(I18n::tr("VALIDATOR_TRACKLIST"));
        }

    }

    /**
     * @param $code
     * @throws ControllerException
     */
    public function validateRegistrationCode($code) {

        $decoded    = base64_decode($code);

        if ($decoded === false) {
            throw new ControllerException(I18n::tr("VALIDATOR_CODE_INVALID"));
        }

        $object     = json_decode($decoded, true);

        if (is_null($object) || empty($object["email"]) || empty($object["code"])) {
            throw new ControllerException(I18n::tr("VALIDATOR_CODE_INVALID"));
        }

        if (md5($object['email'] . "@radioter.io@" . $object['email']) !== $object['code']) {
            throw new ControllerException(I18n::tr("VALIDATOR_CODE_MATCH"));
        }

    }

    /**
     * @param $category
     * @throws ControllerException
     */
    public function validateStreamCategory($category) {

        Category::getByID($category)->justThrow(
            new ControllerException(I18n::tr("VALIDATOR_INVALID_CATEGORY", [$category]))
        );

    }

    /**
     * @param $access
     * @throws ControllerException
     */
    public function validateStreamAccess($access) {

        if (array_search($access, ['PUBLIC', 'UNLISTED', 'PRIVATE']) === false) {
            throw new ControllerException(I18n::tr("VALIDATOR_INVALID_ACCESS", [$access]));
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
            throw new ControllerException(I18n::tr("VALIDATOR_IMAGE_MIME"));
        }
    }

    public function validateTrackColor($color) {
        Color::getByID($color)
            ->justThrow(new ControllerException(I18n::tr("VALIDATOR_TRACK_COLOR", [$color])));
    }

}