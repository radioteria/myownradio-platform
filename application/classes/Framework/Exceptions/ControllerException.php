<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 20:42
 */

namespace Framework\Exceptions;

use Framework\Services\Locale\I18n;

/**
 * Class ControllerException
 * @package Framework\Exceptions
 * @localized 21.05.2015
 */
class ControllerException extends \Exception {

    private $myMessage = null;
    private $myData = [];
    private $myHttpCode = 200;
    private $myStatus = 0;

    /**
     * @param null $message
     * @param null $data
     * @param int $status
     */
    function __construct($message = null, $data = null, $status = 0) {
        $this->myMessage = $message;
        $this->myData = $data;
        $this->myStatus = $status;
    }

    /**
     * @param null $message
     * @param null $data
     * @param int $status
     * @return ControllerException
     */
    public static function of($message = null, $data = null, $status = 0) {
        return new self($message, $data, $status);
    }

    /**
     * @param string $key
     * @param mixed $data
     * @param int $status
     * @return ControllerException
     */
    public static function tr($key, $data = null, $status = 0) {
        return new self(I18n::tr($key), $data, $status);
    }

    /**
     * @return ControllerException
     */
    public static function noImageAttached() {
        return new self(I18n::tr("ERROR_NO_IMAGE_ATTACHED"));
    }

    /**
     * @return array|null
     */
    public function getMyData() {
        return $this->myData;
    }

    /**
     * @return string|null
     */
    public function getMyMessage() {
        return $this->myMessage;
    }

    /**
     * @return int
     */
    public function getMyStatus() {
        return $this->myStatus;
    }

    /**
     * @param $name
     * @return ControllerException
     */
    public static function noArgument($name) {
        return new self(I18n::tr("ERROR_NO_ARGUMENT_SPECIFIED", ["arg" => $name]));
    }

    /**
     * @param $key
     * @return ControllerException
     */
    public static function noStream($key) {
        return new self(I18n::tr("ERROR_STREAM_NOT_FOUND", ["arg" => $key]));
    }

    /** @return ControllerException */
    public static function noStreams() {
        return new self(I18n::tr("ERROR_NO_STREAMS"));
    }

    /**
     * @param $id
     * @return ControllerException
     */
    public static function noUser($id) {
        return new self(I18n::tr("ERROR_USER_NOT_FOUND", ["id" => $id]));
    }

    /**
     * @param $id
     * @return ControllerException
     */
    public static function noTrack($id) {
        return new self(I18n::tr("ERROR_TRACK_NOT_FOUND", ["id" => $id]));
    }

    /**
     * @param $plan_id
     * @return ControllerException
     */
    public static function noAccountPlan($plan_id) {
        return new self(I18n::tr("ERROR_NO_ACCOUNT_PLAN", ["id" => $plan_id]));
    }

}