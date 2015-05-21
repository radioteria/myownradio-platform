<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.04.15
 * Time: 22:57
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Letter
 * @package Objects
 * @table mor_email_queue
 * @key id
 * @do_unsent status = 0
 */
class Letter
    extends ActiveRecordObject
    implements ActiveRecord {

    protected $id;
    protected $subject;
    protected $to;
    protected $body;
    protected $ip;
    protected $time;
    protected $status;

    /**
     * @return mixed
     */
    public function getBody() {
        return $this->body;
    }

    /**
     * @return mixed
     */
    public function getFrom() {
        return $this->from;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getIp() {
        return $this->ip;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getSubject() {
        return $this->subject;
    }

    /**
     * @return mixed
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * @return mixed
     */
    public function getTo() {
        return $this->to;
    }

    /**
     * @param mixed $body
     */
    public function setBody($body) {
        $this->body = $body;
    }

    /**
     * @param mixed $from
     */
    public function setFrom($from) {
        $this->from = $from;
    }

    /**
     * @param mixed $ip
     */
    public function setIp($ip) {
        $this->ip = $ip;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status) {
        $this->status = $status;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @param mixed $time
     */
    public function setTime($time) {
        $this->time = $time;
    }

    /**
     * @param mixed $to
     */
    public function setTo($to) {
        $this->to = $to;
    }

} 