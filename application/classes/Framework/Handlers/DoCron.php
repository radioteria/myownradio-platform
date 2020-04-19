<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 12:36
 */

namespace Framework\Handlers;

use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\Date;
use Framework\Services\HttpRequest;
use Framework\Services\Mail\MailQueue;

class DoCron implements Controller
{
    const SEND_QUEUE_SIZE = 5;

    public function doCron(MailQueue $queue)
    {
        FSFile::deleteUnused();

        /* Every minute */
        $queue->send(self::SEND_QUEUE_SIZE);
    }
}
