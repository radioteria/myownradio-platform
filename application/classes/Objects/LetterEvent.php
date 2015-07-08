<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 08.07.2015
 * Time: 9:39
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class EmailsSent
 * @package Objects
 * @table mor_letter_event
 * @key id
 */
class LetterEvent extends ActiveRecordObject implements ActiveRecord {

    private $id, $to, $subject, $date, $hash;

    /**
     * @return mixed
     */
    public function getId() {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTo() {
        return $this->to;
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
    public function getDate() {
        return $this->date;
    }

    /**
     * @return mixed
     */
    public function getHash() {
        return $this->hash;
    }

    /**
     * @param $to
     */
    public function setTo($to) {
        $this->to = $to;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject) {
        $this->subject = $subject;
    }

    /**
     * @param mixed $date
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash) {
        $this->hash = $hash;
    }


    function beforeUpdate() {

        $this->date = $this->date ?: time();

    }


}