<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 09.12.14
 * Time: 17:40
 */

class Mailer {

    private $address = [];
    private $subject = "";
    private $sender = "";
    private $body = "";
    private $contentType = "text/plain";

    function __construct(/* String */ $sender) {
        if(empty($sender)) {
            throw new Exception("Sender must be specified");
        }
        $this->sender = $sender;
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
        $headers = "Content-Type: " . $this->contentType . "\r\n\r\n";
        $res = mail(implode(",", $this->address), $this->subject, $this->body, $headers, "-f" . $this->sender);
        if ($res === 0) {
            throw new Exception("Send Mail Error");
        }
    }

} 