<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 20:42
 */

namespace Framework\Exceptions;

use Framework\Services\Locale\I18n;

class ControllerException extends \Exception {

    private $myMessage = null;
    private $myData = [];
    private $myHttpCode = 200;

    function __construct($message = null, $data = null, $code = 200) {
        $this->myMessage = $message;
        $this->myData = $data;
        $this->myHttpCode = $code;
    }

    public static function of($message = null, $data = null) {
        return new self($message, $data);
    }

    public static function noImageAttached() {
        return new self(I18n::tr("CONTROLLER_EX_NO_IMAGE_ATTACHED"));
    }

    public function getMyData() {
        return $this->myData;
    }

    public function getMyMessage() {
        return $this->myMessage;
    }

    public static function noArgument($name) {
        return new self(I18n::tr("CEX_NO_ARGUMENT_SPECIFIED", ["arg" => $name]));
    }

    public static function noStream($key) {
        return new self(I18n::tr("CEX_NO_STREAM_FOUND", ["arg" => $key]));
    }

    public static function noUser($id) {
        return new self(I18n::tr("CEX_NO_USER_FOUND", ["id" => $id]));
    }

    public static function noTrack($id) {
        return new self(I18n::tr("CEX_NO_TRACK_FOUND", ["id" => $id]));
    }

}