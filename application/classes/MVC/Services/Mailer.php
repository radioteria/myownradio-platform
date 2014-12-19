<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 09.12.14
 * Time: 17:40
 */

namespace MVC\Services;

use Exception;

class Mailer {

    private $address = [];
    private $subject = "";
    private $sender = "";
    private $body = "";
    private $contentType = "text/plain";
    private $senderName = "The MyOwnRadio Team";

    function __construct($sender, $name) {
        if(empty($sender) || empty($name)) {
            throw new Exception("Sender address and name must be specified");
        }
        $this->sender = $sender;
        $this->senderName = $name;
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

        $senderString   = sprintf("%s <%s>", $this->senderName, $this->sender);
        $flag           = "-f" . $this->sender;

        $headers        = "Content-Type: " . $this->contentType . "\r\n";
        $headers       .= "From: {$senderString}\r\n";

        $targets        = implode(",", $this->address);

        $result = mail($targets, $this->subject, $this->body, $headers, $flag);

        if ($result == false) {
            throw new Exception(sprintf("Message to '%s' could not be sent", $targets));
        }

    }

} 