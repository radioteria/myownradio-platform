<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:10
 */

namespace Framework\Models;


use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Services\Database;
use Framework\Services\HttpGet;
use Framework\Services\HttpPost;
use Framework\Services\HttpSession;
use Tools\Singleton;

class AuthUserModel extends UserModel implements Injectable {

    protected $userToken, $clientId;

    function __construct() {
        $session = $this->getIdBySessionToken();
        $this->clientId = $session["client_id"];
        parent::__construct($session["uid"]);
        parent::touchLastLoginDate();
    }

    private function getIdBySessionToken() {

        $exception = UnauthorizedException::noPermission();

//        $token = HttpSession::getInstance()->get("TOKEN")->getOrElse(
//            HttpPost::getInstance()->getParameter("token")->getOrElse(
//                HttpGet::getInstance()->getParameter("token")->getOrElseThrow($exception)
//            )
//        );

        $token = HttpSession::getInstance()->get("TOKEN")->getOrElse($exception);

        $session = Database::doInConnection(function (Database $db) use ($token, $exception) {

            $query = $db->getDBQuery()
                ->selectFrom("r_sessions a")->innerJoin("r_users b", "a.uid = b.uid")
                ->select("*")
                ->where("a.token", $token);

            $session = $db->fetchOneRow($query)->getOrElseThrow($exception);

            $this->userToken = $token;

            return $session;

        });

        return $session;

    }

    /**
     * @return string
     */
    public function getToken() {
        return $this->userToken;
    }

    /**
     * @return string
     */
    public function getClientId() {
        return $this->clientId;
    }


    public static function getAuthorizedUserID() {
        try {
            return self::getInstance()->getID();
        } catch (UnauthorizedException $e) {
            return null;
        }
    }

}