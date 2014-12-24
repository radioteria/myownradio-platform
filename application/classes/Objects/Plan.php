<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:43
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Plan
 * @package Model\ActiveRecords
 * @table r_limitations
 * @key plan_id
 */
class Plan extends ActiveRecordObject implements ActiveRecord {

    protected $plan_id, $plan_name, $upload_limit, $streams_max;

    /**
     * @return mixed
     */
    public function getID() {
        return $this->plan_id;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->plan_name = $name;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->plan_name;
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