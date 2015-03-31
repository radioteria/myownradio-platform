<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 09.12.14
 * Time: 17:40
 */

namespace Framework\Services;

use Exception;
use Framework\Services\Mail\MailQueue;

class Mailer {

    private $address = [];
    private $subject = "";
    private $sender = "";
    private $body = "";
    private $contentType = "text/plain";
    private $senderName = "The MyOwnRadio Team";
    private $senderIp = "";
    private $created;

    function __construct($sender, $name) {
        if (empty($sender) || empty($name)) {
            throw new Exception("Sender address and name must be specified");
        }
        $this->sender = $sender;
        $this->senderName = $name;
        $this->senderIp = HttpRequest::getInstance()->getRemoteAddress();
        $this->created = time();
    }

    public function setBody($body) {
        $this->body = $body;
        return $this;
    }

    public function setContentType($contentType) {
        $this->contentType = $contentType;
        return $this;
    }

    public function setSubject($subject) {
        $this->subject = $subject;
        return $this;
    }

    public function addAddress($address) {
        $this->address[] = $address;
        return $this;
    }

    public function send() {

        $senderString = sprintf("%s <%s>", $this->senderName, $this->sender);
        $flag = "-f" . $this->sender;

        $headers = "Content-Type: " . $this->contentType . "\r\n";
        $headers .= "From: {$senderString}\r\n";

        $targets = implode(",", $this->address);

        $result = mail($targets, $this->subject, $this->body, $headers, $flag);

        if ($result == false) {
            logger(sprintf("Message to '%s' could not be sent", $targets));
        }

    }

    public function queue() {
        MailQueue::getInstance()->add($this);
    }

    /**
     * @return string
     */
    public function getSenderIp() {
        return $this->senderIp;
    }

    /**
     * @return mixed
     */
    public function getCreated() {
        return $this->created;
    }



} 