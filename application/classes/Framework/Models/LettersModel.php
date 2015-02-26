<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:21
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\Services\Mailer;
use Framework\Template;

class LettersModel {

    public static function sendRegistrationLetter($email) {

        $code = md5($email . "@myownradio.biz@" . $email);
        $confirm = base64_encode(json_encode(['email' => $email, 'code' => $code]));

        $template = new Template("application/tmpl/reg.request.mail.tmpl");
        $mailer = new Mailer(REG_MAIL, REG_NAME);

        $template->addVariable("confirm", $confirm, false);

        $mailer->addAddress($email);
        $mailer->setContentType("text/html");
        $mailer->setSubject("Registration on myownradio.biz");
        $mailer->setBody($template->makeDocument());

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

    public static function sendRegistrationCompleted($email) {

        $template = new Template("application/tmpl/reg.complete.tmpl");
        $mailer = new Mailer(REG_MAIL, REG_NAME);

        $mailer->addAddress($email);
        $mailer->setContentType("text/html");
        $mailer->setSubject("Registration on myownradio.biz completed");
        $mailer->setBody($template->makeDocument());

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }
        
    }

    public static function sendResetPasswordLetter($id) {


        $user = new UserModel($id);

        $code = base64_encode(json_encode(["login" => $user->getLogin(), "password" => $user->getPassword()]));

        $template = new Template("application/tmpl/reg.reset.password.tmpl");
        $mailer = new Mailer(REG_MAIL, REG_NAME);

        $template->addVariable("name", $user->getDisplayName(), false);
        $template->addVariable("login", $user->getLogin(), false);
        $template->addVariable("code", $code, false);

        $mailer->addAddress($user->getEmail());
        $mailer->setContentType("text/html");
        $mailer->setSubject("Reset password on myownradio.biz");
        $mailer->setBody($template->makeDocument());

        try {
            $mailer->send();
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

} 