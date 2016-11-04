<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:21
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\Services\Locale\L10n;
use Framework\Services\Letter;
use Framework\Template;

class LettersModel {

    public static function sendRegistrationLetter($email) {

        $i18n = L10n::getInstance();

        $code = md5($email . "@myownradio.biz@" . $email);
        $confirm = base64_encode(json_encode(['email' => $email, 'code' => $code]));

        $template = new Template("locale/{$i18n->getLocale()}.reg.request.mail.tmpl");
        $mailer = new Letter(REG_MAIL, REG_NAME);

        $template->addVariable("confirm", $confirm, false);

        $mailer->addAddress($email);
        $mailer->setContentType("text/html");
        $mailer->setSubject($i18n->get("EMAIL_REG_TITLE"));
        $mailer->setBody($template->render());

//        $mailer->queue();

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

    public static function sendRegistrationCompleted($email) {

        $i18n = L10n::getInstance();

        $template = new Template("locale/{$i18n->getLocale()}.reg.complete.tmpl");
        $mailer = new Letter(REG_MAIL, REG_NAME);

        $mailer->addAddress($email);
        $mailer->setContentType("text/html");
        $mailer->setSubject($i18n->get("EMAIL_REG_COMPLETED"));
        $mailer->setBody($template->render());

//        $mailer->queue();
//
        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }
        
    }

    public static function sendResetPasswordLetter($id) {

        $i18n = L10n::getInstance();

        $user = new UserModel($id);

        $code = base64_encode(json_encode(["login" => $user->getLogin(), "password" => $user->getPassword()]));

        $template = new Template("locale/{$i18n->getLocale()}.reset.password.tmpl");
        $mailer = new Letter(REG_MAIL, REG_NAME);

        $template->addVariable("name", $user->getDisplayName(), false);
        $template->addVariable("login", $user->getLogin(), false);
        $template->addVariable("code", $code, false);

        $mailer->addAddress($user->getEmail());
        $mailer->setContentType("text/html");
        $mailer->setSubject($i18n->get("EMAIL_PASSWORD_RESET"));
        $mailer->setBody($template->render());

//        $mailer->queue();

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

} 