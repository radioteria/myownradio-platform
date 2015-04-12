<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 23.03.15
 * Time: 21:32
 */

namespace Framework\Handlers\api\v2\user;


use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Framework\Controller;
use Framework\Exceptions\UnauthorizedException;
use Framework\Models\LettersModel;
use Framework\Models\UserModel;
use Framework\Models\UsersModel;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Mailer;
use Objects\User;

class DoFbLogin implements Controller {

    const FB_USER_PREFIX = "fbuser_";

    public function doPost(HttpPost $post, JsonResponse $response, UsersModel $model) {

        $token = $post->getRequired("token");

        $session = new FacebookSession($token);

        if ($session) {

            /** @var GraphUser $user_profile */
            $user_profile = (new FacebookRequest(
                $session, 'GET', '/me?fields=email,name'
            ))->execute()->getGraphObject(GraphUser::className());

            User::getByFilter("login = ? OR mail = ?", [self::FB_USER_PREFIX.$user_profile->getId(), $user_profile->getEmail()])

                ->then(function (User $user) use ($response, $model) {

                    /** @var UserModel $userModel */
                    $userModel = $model->authorizeById($user->getID());

                    $response->setData($userModel->toRestFormat());

                })

                ->otherwise(function () use ($user_profile, $response, $model) {

                    $user = new User();
                    $user->setLogin(self::FB_USER_PREFIX.$user_profile->getId());
                    $user->setPassword(NULL);
                    $user->setName($user_profile->getName());
                    $user->setAvatar(NULL);
                    $user->setCountryId(NULL);
                    $user->setEmail($user_profile->getEmail());
                    $user->setInfo("");
                    $user->setRegistrationDate(time());
                    $user->setRights(1);
                    $user->setPermalink(NULL);
                    $user->save();

                    UsersModel::getInstance()->createUserDirectory($user);

                    $notify = new Mailer("no-reply@myownradio.biz", "myownradio.biz");
                    $notify->addAddress("roman@homefs.biz");
                    $notify->setSubject("You have new user");
                    $notify->setBody(sprintf("Hello! You have a new user from facebook '%s' (%s).",
                        $user->getName(), $user->getEmail()));
                    $notify->send();

                    LettersModel::sendRegistrationCompleted($user_profile->getEmail());

                    /** @var UserModel $userModel */
                    $userModel = $model->authorizeById($user->getID());

                    $response->setData($userModel->toRestFormat());

                });

        } else {
            throw UnauthorizedException::noPermission();
        }

    }
} 