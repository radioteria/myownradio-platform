<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 9:32
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
use MVC\Services\HttpRequest;
use MVC\Services\HttpSession;
use MVC\Services\Mailer;
use MVC\Template;

class Users {

    /**
     * @param $login
     * @param $password
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

        return User::getInstance();

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
            [$userId, $clientAddress, $token, $clientUserAgent, $sessionId])->getOrElseThrow(
                ControllerException::databaseError("Token Creator"));

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

    public static function sendRegistrationLetter($email) {

        $code = md5($email . "@myownradio.biz@" . $email);
        $confirm = base64_encode(json_encode(['email' => $email, 'code' => $code]));

        $template = new Template("application/tmpl/reg.request.mail.tmpl");
        $mailer = new Mailer("no-reply@myownradio.biz", "The MyOwnRadio Team");

        $template->addVariable("confirm", $confirm, true);

        $mailer->addAddress($email);
        $mailer->setContentType("text/html");
        $mailer->setSubject("Registration on myownradio.biz");
        $mailer->setBody($template->makeDocument());

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }
}