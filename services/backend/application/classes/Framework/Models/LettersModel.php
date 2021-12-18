<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 13:21
 */

namespace Framework\Models;


use app\Services\EmailService;
use Framework\Exceptions\ControllerException;
use Framework\Services\Locale\L10n;
use Framework\Template;

class LettersModel {

    public static function sendRegistrationLetter($email) {

        $i18n = L10n::getInstance();
        $emailService = EmailService::getInstance();

        $code = md5($email . "@myownradio.biz@" . $email);
        $confirm = base64_encode(json_encode(['email' => $email, 'code' => $code]));

        $template = new Template("locale/{$i18n->getLocale()}.reg.request.mail.tmpl");
        $template->addVariable("confirm", $confirm);

        try {
            $emailService->send($email, $i18n->get("EMAIL_REG_TITLE"), $template->render());
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

    public static function sendRegistrationCompleted($email) {

        $i18n = L10n::getInstance();
        $emailService = EmailService::getInstance();

        $template = new Template("locale/{$i18n->getLocale()}.reg.complete.tmpl");

        try {
            $emailService->send($email, $i18n->get("EMAIL_REG_COMPLETED"), $template->render());
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }
        
    }

    public static function sendResetPasswordLetter($id) {

        $i18n = L10n::getInstance();
        $emailService = EmailService::getInstance();

        $user = new UserModel($id);

        $code = base64_encode(json_encode(["login" => $user->getLogin(), "password" => $user->getPassword()]));

        $template = new Template("locale/{$i18n->getLocale()}.reset.password.tmpl");

        $template->addVariable("name", $user->getDisplayName(), false);
        $template->addVariable("login", $user->getLogin(), false);
        $template->addVariable("code", $code, false);

        try {
            $emailService->send($user->getEmail(), $i18n->get("EMAIL_PASSWORD_RESET"), $template->render());
        } catch (\Exception $exception) {
            throw new ControllerException($exception->getMessage());
        }

    }

} 
