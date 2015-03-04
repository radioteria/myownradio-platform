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

    const SEND_QUEUE_SIZE = 5;

    public function doCron(HttpRequest $request, Date $date, MailQueue $queue) {

        if ($request->getServerAddress() != $request->getRemoteAddress()) {
            throw ControllerException::noPermission();
        }

        /* Every hour */
        if (0 == $date->getMinutes()) {
            error_log("Hourly cron engaged.");
        }

        /* Every minute */
        $queue->send(self::SEND_QUEUE_SIZE);

    }
} 