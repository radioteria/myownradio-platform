<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 15:13
 */

namespace Framework\Services\Mail;


use Framework\Injector\Injectable;
use Framework\Services\Mailer;
use Framework\Services\Redis;
use Tools\Singleton;
use Tools\SingletonInterface;

class MailQueue implements Injectable, SingletonInterface, \Countable {
    use Singleton;

    /** @var Redis $redis */
    private $redis;

    function __construct() {
        $this->redis = Redis::getInstance();
    }

    /**
     * Adds Mailer object into send queue
     * @param Mailer $message
     */
    public function add(Mailer $message) {
        $this->redis->applyObject("mail_queue", function (&$array) use ($message) {
            $array[] = $message;
        }, []);
    }

    /**
     * Sends first $count of queued emails
     * @param $count
     */
    public function send($count) {
        $this->redis->applyObject("mail_queue", function (&$array) use ($count) {
            while (count($array) && ($count--)) {
                /** @var Mailer $mailer */
                $mailer = array_shift($array);
                $mailer->send();
            }
        }, []);
    }

    public function count() {
        return count($this->redis->getObject("mail_queue")->getOrElse([]));
    }
} 