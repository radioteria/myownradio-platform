<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 16:38
 */

use Framework\Events\RegistrationSuccessfulPublisher;
use Framework\Services\Mailer;
use Objects\User;

/* Registration successful event subscriber */
RegistrationSuccessfulPublisher::getInstance()->subscribe(function (User $user) {

    $admin = new Mailer("no-reply@myownradio.biz", "myownradio.biz");
    $admin->addAddress("roman@homefs.biz");
    $admin->setSubject("New user registered on MyOwnRadio!");
    $admin->setBody(sprintf("Hello! You have a new user on MyOwnRadio - %s.", $user->getLogin()));
    $admin->send();

    //LettersModel::sendRegistrationCompleted($user_profile->getEmail());

});

