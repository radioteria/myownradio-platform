<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:36
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\Date;
use Framework\Services\HttpRequest;
use Framework\Services\Mail\MailQueue;

class DoCron implements Controller {
    const MAIL_QUEUE = 5;

    public function doCron(HttpRequest $request, Date $date, MailQueue $queue) {

        if ($request->getServerAddress() != $request->getRemoteAddress()) {
            throw ControllerException::noPermission();
        }

        /* Cron body is here */
        if (0 == $date->getMinutes()) {
            /* Do every hour */
            error_log("Hourly cron engaged.");
        }

        /* Mail queue rotate */
        $queue->send(self::MAIL_QUEUE);
    }
} 