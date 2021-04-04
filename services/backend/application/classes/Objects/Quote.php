<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 12:24
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Quote
 * @package Objects
 * @table mor_limits
 * @key limit_id
 */
class Quote extends ActiveRecordObject implements ActiveRecord {
    private
        $limit_id,
        $streams_max,
        $time_max;

    /**
     * @return mixed
     */
    public function getLimitId() {
        return $this->limit_id;
    }

    /**
     * @return mixed
     */
    public function getStreamsMax() {
        return $this->streams_max;
    }

    /**
     * @return mixed
     */
    public function getTimeMax() {
        return $this->time_max;
    }

    /**
     * @param mixed $streams_max
     */
    public function setStreamsMax($streams_max) {
        $this->streams_max = $streams_max;
    }

    /**
     * @param mixed $time_max
     */
    public function setTimeMax($time_max) {
        $this->time_max = $time_max;
    }


} 