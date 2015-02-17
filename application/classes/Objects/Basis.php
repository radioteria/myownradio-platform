<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 15:01
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;


/**
 * Class Basis
 * @package Model\ActiveRecords
 * @table mor_payment_basis
 * @key id
 * @deprecated
 */
class Basis extends ActiveRecordObject implements ActiveRecord {

    protected $basis_id, $basis_info, $basis_duration;

    /**
     * @return mixed
     */
    public function getDuration() {
        return $this->basis_duration;
    }

    /**
     * @return mixed
     */
    public function getId() {
        return $this->basis_id;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->basis_info;
    }

    /**
     * @param mixed $duration
     */
    public function setDuration($duration) {
        $this->basis_duration = $duration;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info) {
        $this->basis_info = $info;
    }


}