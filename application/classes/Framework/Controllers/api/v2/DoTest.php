<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 11:53
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Framework\Services\Mail\MailQueue;
use Framework\Services\Mailer;

class DoTest implements Controller {
    public function doGet(MailQueue $queue) {
        $test = new Mailer("robot@myownradio.biz", "MYOWNRADIO ROBOT");
        $test->addAddress("roman@homefs.biz");
        $test->setSubject("Email queue test message");
        $test->setBody("Hello! This is a email queue test message.");

        $queue->add($test);

        echo "Messages queued: " . count($queue);
    }
} 