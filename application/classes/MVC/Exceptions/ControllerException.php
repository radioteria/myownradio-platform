<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 20:42
 */

namespace MVC\Exceptions;

class ControllerException extends \Exception {

    private $myMessage = null;
    private $myData = [];

    function __construct($message = null, $data = null) {
        $this->myMessage = $message;
        $this->myData = $data;
    }

    public function getMyData() {
        return $this->myData;
    }

    public function getMyMessage() {
        return $this->myMessage;
    }

    public static function noArgument($name) {
        return new self(sprintf("No value for argument '%s' specified", $name));
    }

    public static function databaseError() {
        return new self(sprintf("Something wrong with database"));
    }

    public static function noStream($name) {
        return new self(sprintf("Stream with key '%s' not found", $name));
    }

    public static function noPermission() {
        return new self("You have no permission to access this resource");
    }

    public static function noEntity($name) {
        return new self("No entity with key '%s' found", $name);
    }
}