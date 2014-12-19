<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:43
 */

namespace Model\ActiveRecords;

/**
 * Class Plan
 * @package Model\ActiveRecords
 * @table r_limitations
 * @key level
 */
class Plan implements ActiveRecord {

    use ActiveRecordObject;

    protected $level, $name, $upload_limit, $streams_max, $price;

    /**
     * @return mixed
     */
    public function getID() {
        return $this->level;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price) {
        $this->price = $price;
    }

    /**
     * @return mixed
     */
    public function getPrice() {
        return $this->price;
    }

    /**
     * @param mixed $streams_max
     */
    public function setStreamsMax($streams_max) {
        $this->streams_max = $streams_max;
    }

    /**
     * @return mixed
     */
    public function getStreamsMax() {
        return $this->streams_max;
    }

    /**
     * @param mixed $upload_limit
     */
    public function setUploadLimit($upload_limit) {
        $this->upload_limit = $upload_limit;
    }

    /**
     * @return mixed
     */
    public function getUploadLimit() {
        return $this->upload_limit;
    }



} 