<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:10
 */

namespace Framework\Models;


use Framework\Exceptions\AccessException;
use Framework\Exceptions\Auth\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Services\Database;
use Framework\Services\HttpSession;

class AuthUserModel extends UserModel implements Injectable {

    protected $userToken, $clientId;

    function __construct() {
        $session = $this->getIdBySessionToken();
        $this->clientId = $session["client_id"];
        parent::__construct($session["uid"]);
        parent::touchLastLoginDate();
    }

    private function getIdBySessionToken() {

        $token = HttpSession::getInstance()->get("TOKEN")->getOrElse(UnauthorizedException::class);

        $session = Database::doInConnection(function (Database $db) use ($token) {

            $query = $db->getDBQuery()
                ->selectFrom("r_sessions a")->innerJoin("r_users b", "a.uid = b.uid")
                ->select("*")
                ->where("a.token", $token);

            $session = $db->fetchOneRow($query)->getOrThrow(UnauthorizedException::class);

            $this->userToken = $token;

            return $session;

        });

        return $session;

    }

    public static function getAuthorizedUserID() {
        try {
            return self::getInstance()->getID();
        } catch (AccessException $e) {
            return null;
        }
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

}