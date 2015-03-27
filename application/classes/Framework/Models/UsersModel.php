<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 9:32
 */

namespace Framework\Models;


use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Services\Config;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpRequest;
use Framework\Services\HttpSession;
use Framework\Services\InputValidator;
use Framework\Services\Mailer;
use Objects\User;
use Tools\File;
use Tools\Folders;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class UsersModel implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param string $login
     * @param string $password
     * @throws \Framework\Exceptions\UnauthorizedException
     * @return UserModel
     */
    public function authorizeByLoginPassword($login, $password) {

        $session = HttpSession::getInstance();

        // Try to find user specified by login or email
        $user = DBQuery::getInstance()
            ->selectFrom("r_users")
            ->where("login = :key OR mail = :key", [ ":key" => $login ])
            ->fetchOneRow()
            ->getOrElseThrow(UnauthorizedException::noUserExists($login));

        //$md5Password = md5($user["login"] . $password);

        if (!password_verify($password, $user["password"])) {
            throw UnauthorizedException::wrongPassword();
        }

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent, $session->getSessionId());

        $session->set("TOKEN", $token);

        return UserModel::getInstance($user["uid"]);

    }

    /**
     * @param $userId
     * @param $clientAddress
     * @param $clientUserAgent
     * @param $sessionId
     * @return string
     */
    private function createToken($userId, $clientAddress, $clientUserAgent, $sessionId) {

        $token = Database::doInConnection(function (Database $db) use ($userId, $clientAddress, $clientUserAgent, $sessionId) {

            do {
                $token = md5($userId . $clientAddress . rand(1, 1000000) . "tokenizer" . time());
            } while ($db->fetchOneColumn("SELECT COUNT(*) FROM r_sessions WHERE token = ?", [$token])->get() > 0);

            $query = $db->getDBQuery()->into("r_sessions");
            $query->values("uid", $userId);
            $query->values("ip", $clientAddress);
            $query->values("token", $token);
            $query->values("permanent", 1);
            $query->values("authorized = NOW()");
            $query->values("http_user_agent", $clientUserAgent);
            $query->values("session_id", $sessionId);
            $query->values("expires = NOW() + INTERVAL 1 YEAR");

            $db->executeInsert($query);

            return $token;

        });

        return $token;

    }

    /**
     * @return void
     */
    public function logout() {

        $session = HttpSession::getInstance();

        $session->get("TOKEN")->then(function ($token) {
            DBQuery::getInstance()->deleteFrom("r_sessions", "token", $token)->update();
        });

    }

    /**
     * @param $id
     * @return mixed
     */
    public function authorizeById($id) {

        $session = HttpSession::getInstance();

        $user = Database::doInConnection(function (Database $db) use ($id) {
            return $db->fetchOneRow("SELECT * FROM r_users WHERE uid = ?", [$id])
                ->getOrElseThrow(UnauthorizedException::noUserExists($id));
        });

        $clientAddress = HttpRequest::getInstance()->getRemoteAddress();
        $clientUserAgent = HttpRequest::getInstance()->getHttpUserAgent()->getOrElse("None");

        $token = self::createToken($user["uid"], $clientAddress, $clientUserAgent, $session->getSessionId());

        $session->set("TOKEN", $token);

        return UserModel::getInstance($id);

    }

    /**
     * @param $code
     * @param $login
     * @param $password
     * @param $name
     * @param $info
     * @param $permalink
     * @param $country
     * @return UserModel
     */
    public function completeRegistration($code, $login, $password, $name, $info, Optional $permalink, $country) {

        $validator = InputValidator::getInstance();

        $email = self::parseRegistrationCode($code);
        $crypt = password_hash($password, PASSWORD_DEFAULT);

        $validator->validateUniqueUserEmail($email);

        $newUser = new User();

        $newUser->setEmail($email);
        $newUser->setLogin($login);
        $newUser->setPassword($crypt);
        $newUser->setName($name);
        $newUser->setInfo($info);
        $newUser->setPermalink($permalink);
        $newUser->setRights(1);
        $newUser->setRegistrationDate(time());
        $newUser->setCountryId($country);

        $newUser->save();

        $this->createUserDirectory($newUser);

        $notify = new Mailer("no-reply@myownradio.biz", "myownradio.biz");
        $notify->addAddress("roman@homefs.biz");
        $notify->setSubject("You have new user");
        $notify->setBody(sprintf("Hello! You have a new user '%s' (%s).", $login, $email));
        $notify->send();

        LettersModel::sendRegistrationCompleted($newUser->getEmail());

        return new UserModel($newUser->getID());

    }

    /**
     * @param $code
     * @param $password
     */
    public function completePasswordReset($code, $password) {

        $credentials = self::parseResetPasswordCode($code);

        $user = new UserModel($credentials["login"], $credentials["password"]);

        $user->changePasswordNow($password);

    }

    /**
     * @param User $id
     */
    public function createUserDirectory(User $id) {

        $path = new File(Folders::getInstance()->generateUserContentFolder($id));

        error_log($path->path());

        if (! $path->exists()) {
            $path->createNewDirectory(NEW_DIR_RIGHTS, true);
        }

    }

    /**
     * @param $code
     * @return mixed
     * @throws \Framework\Exceptions\ControllerException
     */
    public function parseRegistrationCode($code) {

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
     * @throws \Framework\Exceptions\ControllerException
     */
    public function parseResetPasswordCode($code) {

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