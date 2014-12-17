<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 13:10
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
use MVC\Services\HttpSession;
use MVC\Services\Injectable;
use Tools\Singleton;

class AuthorizedUser extends User {

    use Injectable;

    function __construct() {

        $uid = $this->getIdBySessionToken();

        parent::__construct($uid);

    }

    public function getIdBySessionToken() {

        $exception = ControllerException::noPermission();

        $token = HttpSession::getInstance()->get("TOKEN")->getOrElseThrow($exception);

        $uid = Database::doInConnection(function (Database $db) use ($token, $exception) {

            $query = $db->getDBQuery()
                ->selectFrom("r_sessions a")->leftJoin("r_users b", "a.uid = b.uid")
                ->where("a.token", $token);

            return $db->fetchOneColumn($query)
                ->getOrElseThrow($exception);

        });

        return $uid;

    }

}