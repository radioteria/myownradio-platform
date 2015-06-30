<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 14:15
 */

namespace Framework\Handlers\api\v2\user\social;


use Facebook\FacebookRequest;
use Facebook\FacebookSDKException;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Framework\Controller;
use Framework\Events\RegistrationSuccessfulPublisher;
use Framework\Exceptions\ControllerException;
use Framework\Models\UsersModel;
use Framework\Services\JsonResponse;
use Objects\User;
use Tools\Optional\Mapper;

class DoFacebook implements Controller {

    const FB_USER_PREFIX = "fbuser_";

    public function doPost(JsonResponse $response, $token, UsersModel $model) {

        $session = new FacebookSession($token);

        try {

            $session->validate();

            /**
             * @var GraphUser $profile
             */
            $profile = (new FacebookRequest($session, 'GET', '/me?fields=email,name'))
                ->execute()
                ->getGraphObject(GraphUser::class);

            $login = self::FB_USER_PREFIX . $profile->getId();
            $email = $profile->getEmail();
            $name  = $profile->getName();

            User::getByFilter("login = ? OR mail = ?", array($login, $email))
                ->otherwise($this->createNewUser($login, $email, $name))
                ->then(RegistrationSuccessfulPublisher::send())
                ->map(Mapper::method("getId"))
                ->map(Mapper::call($model, "authorizeById"))
                ->map(Mapper::method("toRestFormat"))
                ->then(Mapper::call($response, "setData"));

        } catch (FacebookSDKException $e) {

            throw ControllerException::of($e->getMessage());

        }

    }

    /**
     * @param $login
     * @param $email
     * @param $name
     * @return \Closure
     */
    private function createNewUser($login, $email, $name) {

        return function () use ($login, $email, $name) {

            $user = User::getByData([
                "login" => $login,
                "name" => $name,
                "email" => $email,
                "info" => "",
                "password" => null,
                "avatar" => null,
                "country_id" => null,
                "permalink" => null,
                "registration_date" => time(),
                "rights" => 1
            ]);

            $user->save();

            return $user->getId();

        };

    }

}