<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 9:32
 */

namespace Model;


use Model\ActiveRecords\UserAR;
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

        $user = Database::doInConnection(function (Database $db) use ($login, $password) {

            return $db->fetchOneRow("SELECT * FROM r_users WHERE login = ? AND password = ?",
                [$login, md5($login . $password)])->getOrElseThrow(ControllerException::noPermission());

        });

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent,
            $session->getSessionId());

        $session->set("TOKEN", $token);

        return User::getInstance($user["uid"]);

    }

    /**
     * @param $userId
     * @param $clientAddress
     * @param $clientUserAgent
     * @param $sessionId
     * @return string
     */
    private static function createToken($userId, $clientAddress, $clientUserAgent, $sessionId) {

        $token = Database::doInConnection(function (Database $db) use ($userId, $clientAddress, $clientUserAgent, $sessionId) {

            do { $token = md5($userId . $clientAddress . rand(1, 1000000) . "tokenizer" . time()); }
            while ($db->fetchOneColumn("SELECT COUNT(*) FROM r_sessions WHERE token = ?", [$token])->getOrElse(0) > 0);

            $query = $db->getDBQuery()->insertInto("r_sessions");
            $query->values("uid", $userId);
            $query->values("ip", $clientAddress);
            $query->values("token", $token);
            $query->values("permanent", 1);
            $query->values("authorized = NOW()");
            $query->values("http_user_agent", $clientUserAgent);
            $query->values("session_id", $sessionId);
            $query->values("expires = NOW() + INTERVAL 1 YEAR");

            $db->executeInsert($query);
            $db->commit();

            return $token;

        });

        return $token;

    }

    /**
     * @return void
     */
    public static function unAuthorize() {

        $session = HttpSession::getInstance();

        $session->get("TOKEN")->then(function ($token) {
            Database::doInConnection(function (Database $db) use ($token) {
                $db->executeUpdate("DELETE FROM r_sessions WHERE token = ?", [$token]);
            });
        });

        $session->destroy();

    }

    /**
     * @param $id
     * @return mixed
     */
    public static function authorizeById($id) {

        $session = HttpSession::getInstance();

        $user = Database::doInConnection(function (Database $db) use ($id) {
            return $db->fetchOneRow("SELECT * FROM r_users WHERE uid = ?", [$id])
                ->getOrElseThrow(ControllerException::noPermission());
        });

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent, $session->getSessionId());

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
        $md5Password = md5($login . $password);

        $newUser = new UserAR();

        $newUser->setEmail($email);
        $newUser->setLogin($login);
        $newUser->setPassword($md5Password);
        $newUser->setName($name);
        $newUser->setInfo($info);
        $newUser->setPermalink($permalink);
        $newUser->setRights(1);
        $newUser->setRegistrationDate(time());

        $newUser->save();

        self::createUserDirectory($newUser->getID());

        return new User($newUser->getID());

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

        $contentFolder = Config::getInstance()->getSetting("content", "content_folder")
            ->getOrElseThrow(ApplicationException::of("CONTENT FOLDER NOT SPECIFIED"));

        $path = new File(sprintf("%s/ui_%d", $contentFolder, $id));

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

        Database::doInConnection(function (Database $db) use ($decoded) {

            $query = $db->getDBQuery()->selectFrom("r_users");
            $query->where("login", $decoded["login"]);
            $query->where("password", $decoded["password"]);
            $query->select("*");

            $db->fetchOneRow($query)->getOrElseThrow(new ControllerException("Entered security code is not actual"));

        });


        return $decoded;

    }

}