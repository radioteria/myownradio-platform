<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 04.04.15
 * Time: 14:10
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class AudioFormat
 * @package Objects
 * @table opt_valid_format
 * @key format_id
 */
class AudioFormat extends ActiveRecordObject implements ActiveRecord {
    private $format_id;
    private $account_type;
    private $format_string;
    private $format_name;
    private $format_bitrate;

    /**
     * @return mixed
     */
    public function getAccountType() {
        return $this->account_type;
    }

    /**
     * @return mixed
     */
    public function getFormatBitrate() {
        return $this->format_bitrate;
    }

    /**
     * @return mixed
     */
    public function getFormatId() {
        return $this->format_id;
    }

    /**
     * @return mixed
     */
    public function getFormatName() {
        return $this->format_name;
    }

    /**
     * @return mixed
     */
    public function getFormatString() {
        return $this->format_string;
    }

    /**
     * @param mixed $account_type
     */
    public function setAccountType($account_type) {
        $this->account_type = $account_type;
    }

    /**
     * @param mixed $format_bitrate
     */
    public function setFormatBitrate($format_bitrate) {
        $this->format_bitrate = $format_bitrate;
    }

    /**
     * @param mixed $format_name
     */
    public function setFormatName($format_name) {
        $this->format_name = $format_name;
    }

    /**
     * @param mixed $format_string
     */
    public function setFormatString($format_string) {
        $this->format_string = $format_string;
    }


} 