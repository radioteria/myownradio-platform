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
use Tools\Optional\Transform;
use Tools\Singleton;
use Tools\SingletonInterface;

class TestFields implements Injectable, SingletonInterface {
    use Singleton;

    /**
     * @param string $email
     * @return int|bool
     */
    public function testEmail($email) {
        return User::getByFilter("mail = ?", [ $email ])
            ->map(Transform::$userToId)->orFalse();
    }

    /**
     * @param string $login
     * @return int|bool
     */
    public function testLogin($login) {
        $preferences = Preferences::getInstance();
        if (in_array($login, $preferences->get("invalid", "login")->getOrElse([]))) {
            return false;
        }
        return User::getByFilter("login = :id OR mail = :id", [ ":id" => $login ])
            ->map(Transform::$userToId)->orFalse();
    }

    /**
     * @param $permalink
     * @return bool|int
     */
    public function testStreamPermalink($permalink) {
        return Stream::getByFilter("permalink = ?", [ $permalink ])
            ->map(Transform::$userToId)->orFalse();
    }

    /**
     * @param $permalink
     * @return bool|int
     */
    public function testUserPermalink($permalink) {
        $getIdFunc = function (User $user) {
            return $user->getId();
        };
        return User::getByFilter("permalink = ?", [ $permalink ])
            ->map($getIdFunc)->orFalse();
    }
}