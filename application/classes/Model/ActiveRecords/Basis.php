<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 15:01
 */

namespace Model\ActiveRecords;


/**
 * Class Basis
 * @package Model\ActiveRecords
 * @table mor_payment_basis
 * @key id
 */
class Basis implements ActiveRecord {

    use ActiveRecordObject;

    protected $id, $info, $duration;

    /**
     * @return mixed
     */
    public function getDuration() {
        return $this->duration;
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
    public function getInfo() {
        return $this->info;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration) {
        $this->duration = $duration;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info) {
        $this->info = $info;
    }



} 