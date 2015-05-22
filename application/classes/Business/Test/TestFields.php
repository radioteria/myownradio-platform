<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.05.15
 * Time: 10:29
 */

namespace Business\Test;


use Framework\Injector\Injectable;
use Framework\Preferences;
use Objects\Stream;
use Objects\User;
use Tools\Singleton;
use Tools\SingletonInterface;

class TestFields implements Injectable, SingletonInterface {
    use Singleton;

    /**
     * @param string $email
     * @return bool|int
     */
    public function testEmail($email) {
        $result = User::getByFilter("mail = ?", [ $email ])->getOrElseNull();
        return is_null($result) ? false : $result->getId();
    }

    /**
     * @param string $login
     * @return bool|int
     */
    public function testLogin($login) {
        $preferences = Preferences::getInstance();
        if(array_search($login, $preferences->get("invalid", "login")->getOrElse([])) !== false) {
            return false;
        }
        $result = User::getByFilter("login = :id OR mail = :id", [ ":id" => $login ])->getOrElseNull();
        return is_null($result) ? false : $result->getId();
    }

    /**
     * @param $permalink
     * @return bool|int
     */
    public function testStreamPermalink($permalink) {
        /** @var Stream $object */
        $object = Stream::getByFilter("permalink = ?", [ $permalink ])->getOrElseNull();
        return is_null($object) ? false : $object->getId();
    }

    /**
     * @param $permalink
     * @return bool|mixed
     */
    public function testUserPermalink($permalink) {
        /** @var User $object */
        $object = User::getByFilter("permalink = ?", [ $permalink ])->getOrElseNull();
        return is_null($object) ? false : $object->getId();
    }
}