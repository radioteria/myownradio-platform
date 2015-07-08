<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.06.2015
 * Time: 16:38
 */

use Framework\Events\ChannelFavPublisher;
use Framework\Events\RegistrationSuccessfulPublisher;
use Framework\Models\UserModel;
use Framework\Services\Locale\L10n;
use Framework\Services\Mailer;
use Framework\Template;
use Objects\Stream;
use Objects\User;

/* Registration successful event subscriber */
RegistrationSuccessfulPublisher::getInstance()->subscribe(function (User $user) {

    $admin = new Mailer(REG_MAIL, REG_NAME);
    $admin->addAddress("roman@homefs.biz");
    $admin->setSubject("New user registered on MyOwnRadio!");
    $admin->setBody(sprintf("Hello! You have a new user on MyOwnRadio - %s.", $user->getLogin()));
    $admin->send();

    //LettersModel::sendRegistrationCompleted($user_profile->getEmail());

});

ChannelFavPublisher::getInstance()->subscribe(function (Stream $stream, UserModel $model) {

    $locale = L10n::getInstance()->getLocale();

    $message = new Mailer(REG_MAIL, REG_NAME);
    $message->addAddress($model->getEmail());
    $message->setSubject(sprintf("%s added your radio station to bookmarks", $model->getName()));
    $message->setContentType("text/html");

    $template = new Template("locale/{$locale}.channel.bookmarked.tmpl");
    $template->addVariable("user", $model->getName());
    $template->addVariable("station", $stream->getName());
    $template->addVariable("station_url", "/streams/{$stream->getKey()}");

    $message->setBody($template->render());

    $message->send();

});