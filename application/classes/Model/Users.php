<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 9:32
 */

namespace Model;


use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\Config;
use MVC\Services\Database;
use MVC\Services\HttpRequest;
use MVC\Services\HttpSession;
use Tools\File;

class Users {

    /**
     * @param string $login
     * @param string $password
     * @return User
     */
    public static function authorizeByLoginPassword($login, $password) {
        $session = HttpSession::getInstance();

        $user = Database::getInstance()->fetchOneRow("SELECT * FROM r_users WHERE login = ? AND password = ?", [$login,
            md5($login . $password)])->getOrElseThrow(ControllerException::noPermission());

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent,
            $session->getSessionId());

        $session->set("TOKEN", $token);

        return AuthorizedUser::getInstance();

    }

    /**
     * @param $userId
     * @param $clientAddress
     * @param $clientUserAgent
     * @param $sessionId
     * @return string
     */
    private static function createToken($userId, $clientAddress, $clientUserAgent, $sessionId) {

        $database = Database::getInstance();

        do {

            $token = md5($userId . $clientAddress . rand(1, 1000000) . "tokenizer" . time());

        } while ($database->fetchOneColumn(
                "SELECT COUNT(*) FROM r_sessions WHERE token = ?", [$token])->getOrElse(0) > 0);

        $database->executeInsert("INSERT INTO r_sessions SET uid = ?, ip = ?, token = ?, permanent = 1,
            authorized = NOW(), http_user_agent = ?, session_id = ?, expires = NOW() + INTERVAL 1 YEAR",
            [$userId, $clientAddress, $token, $clientUserAgent, $sessionId]);

        return $token;

    }

    /**
     * @return void
     */
    public static function unAuthorize() {

        $session = HttpSession::getInstance();

        $session->get("TOKEN")->then(function ($token) {
            $database = Database::getInstance();
            $database->executeUpdate("DELETE FROM r_sessions WHERE token = ?", [$token]);
        });

        $session->destroy();

    }

    /**
     * @param $id
     * @return mixed
     */
    public static function authorizeById($id) {

        $session = HttpSession::getInstance();

        $user = Database::getInstance()->fetchOneRow("SELECT * FROM r_users WHERE uid = ?", [$id])
            ->getOrElseThrow(ControllerException::noPermission());

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent,
            $session->getSessionId());

        $session->set("TOKEN", $token);

        return User::getInstance();

    }

    /**
     * @param $code
     * @param $login
     * @param $password
     * @param $name
     * @param $info
     * @param $permalink
     * @return User
     */
    public static function completeRegistration($code, $login, $password, $name, $info, $permalink) {

        $email = self::parseRegistrationCode($code);

        $arguments = [$email, $login, md5($login . $password), $name, $info, $permalink, time()];
        $id = Database::getInstance()->executeInsert("INSERT INTO r_users (mail, login, password, name, info,
            permalink, registration_date) VALUES (?, ?, ?, ?, ?, ?, ?)", $arguments);

        self::createUserDirectory($id);

        return new User($id);

    }

    /**
     * @param $code
     * @param $password
     */
    public static function completePasswordReset($code, $password) {

        $credentials = self::parseResetPasswordCode($code);

        $user = new User($credentials["login"], $credentials["password"]);

        $user->changePassword($password);

    }

    /**
     * @param $id
     */
    private static function createUserDirectory($id) {

        $path = new File(sprintf("%s/ui_%d", Config::getInstance()->getSetting("content", "content_folder")
            ->getOrElseThrow(ApplicationException::of("CONTENT FOLDER NOT SPECIFIED")), $id));

        $path->createNewDirectory(NEW_DIR_RIGHTS, true);

    }

    /**
     * @param $code
     * @return mixed
     * @throws \MVC\Exceptions\ControllerException
     */
    public static function parseRegistrationCode($code) {

        $exception = new ControllerException("Entered security code is not correct");

        $json = base64_decode($code);

        if ($json === false) {
            throw $exception;
        }

        $decoded = json_decode($json, true);

        if (is_null($decoded) || empty($decoded["email"]) || empty($decoded["code"])) {
            throw $exception;
        }

        return $decoded["email"];

    }

    /**
     * @param $code
     * @return mixed
     * @throws \MVC\Exceptions\ControllerException
     */
    public static function parseResetPasswordCode($code) {

        $exception = new ControllerException("Entered security code is not correct");

        $json = base64_decode($code);

        if ($json === false) {
            throw $exception;
        }

        $decoded = json_decode($json, true);

        if (empty($decoded["login"]) || empty($decoded["password"])) {
            throw $exception;
        }

        Database::getInstance()->fetchOneRow("SELECT * FROM r_users WHERE login = ? AND password =?", [
            $decoded["login"], $decoded["password"]
        ])->getOrElseThrow(new ControllerException("Entered security code is not actual"));

        return $decoded;

    }

}