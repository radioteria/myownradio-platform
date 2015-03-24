<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 23.03.15
 * Time: 21:32
 */

namespace Framework\Controllers\api\v2\user;


use Facebook\FacebookRequest;
use Facebook\FacebookSession;
use Facebook\GraphUser;
use Framework\Controller;
use Framework\Models\LettersModel;
use Framework\Models\UsersModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpPost;
use Framework\Services\JsonResponse;
use Framework\Services\Mailer;
use Objects\User;

class DoFbLogin implements Controller {

    const FB_USER_PREFIX = "fbuser_";

    public function doPost(HttpPost $post, JsonResponse $response) {

        $token = $post->getRequired("token");

        $session = new FacebookSession($token);

        if ($session) {

            /** @var GraphUser $user_profile */
            $user_profile = (new FacebookRequest(
                $session, 'GET', '/me?fields=email,name'
            ))->execute()->getGraphObject(GraphUser::className());

            $picture = (new FacebookRequest($session, 'GET', '/me/picture?redirect=0&width=720'))->execute()->getResponse();

            User::getByFilter("login = ? OR mail = ?", [self::FB_USER_PREFIX.$user_profile->getId(), $user_profile->getEmail()])

                ->then(function (User $user) {

                    error_log("Log in user from FB");

                    UsersModel::getInstance()->authorizeById($user->getID());

                })

                ->otherwise(function () use ($user_profile, $picture) {

                    error_log("Create new user from FB");

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
                    $user->save();

                    $notify = new Mailer("no-reply@myownradio.biz", "myownradio.biz");
                    $notify->addAddress("roman@homefs.biz");
                    $notify->setSubject("You have new user");
                    $notify->setBody(sprintf("Hello! You have a new user from facebook '%s' (%s).",
                        $user->getName(), $user->getEmail()));
                    $notify->send();

                    LettersModel::sendRegistrationCompleted($user_profile->getEmail());

                    UsersModel::getInstance()->authorizeById($user->getID());

                });

        }

    }
} 