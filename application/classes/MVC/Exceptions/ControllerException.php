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

    public static function databaseError($message) {
        return new self("Something wrong with database", $message);
    }

    public static function noStream($key) {
        return new self(sprintf("No stream with key '%s' found", $key));
    }

    public static function noPermission() {
        return new self("You don't have permission to access this resource");
    }

    public static function noEntity($key) {
        return new self("No entity with key '%s' found", $key);
    }

    public static function noTrack($key) {
        return new self(sprintf("No track with key '%s' found", $key));
    }


}