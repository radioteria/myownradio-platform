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
use Framework\Exceptions\ControllerException;
use Framework\Models\UserModel;
use Framework\Models\UsersModel;
use Framework\Services\JsonResponse;
use Objects\User;

class DoFacebook implements Controller {

    const FB_USER_PREFIX = "fbuser_";

    public $model;
    public $response;

    public function doPost($token, JsonResponse $response, UsersModel $model) {

        /** @var JsonResponse */
        $this->response = $response;

        /** @var UsersModel */
        $this->model = $model;

        $session = new FacebookSession($token);

        try {

            $session->validate();

            /** @var GraphUser $profile */
            $profile = (new FacebookRequest($session, 'GET', '/me?fields=email,name'))
                ->execute()
                ->getGraphObject(GraphUser::class);

            $login = self::FB_USER_PREFIX . $profile->getId();
            $email = $profile->getEmail();

            $update = function(User $user) use (&$model, &$response) {
                /** @var UserModel $userModel */
                $userModel = $model->authorizeById($user->getId());
                $response->setData($userModel->toRestFormat());
            };

            $create = function () {

            };


            User::getByFilter("login = ? OR mail = ?", array($login, $email))
                ->then(function (User $user) {

                });



        } catch (FacebookSDKException $e) {

            throw ControllerException::of($e->getMessage());

        }

    }

}