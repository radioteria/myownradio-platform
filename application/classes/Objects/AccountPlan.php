<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 12:28
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class AccountPlan
 * @package Objects
 * @table mor_plans_view
 * @key plan_id
 * @view
 */
class AccountPlan extends ActiveRecordObject implements ActiveRecord {
    private
        $plan_id,
        $plan_name,
        $plan_duration,
        $plan_value,
        $plan_period,
        $limit_id,
        $streams_max,
        $time_max,
        $min_track_length,
        $max_listeners;

    /**
     * @return mixed
     */
    public function getLimitId() {
        return $this->limit_id;
    }

    /**
     * @return mixed
     */
    public function getPlanDuration() {
        return $this->plan_duration;
    }

    /**
     * @return mixed
     */
    public function getPlanId() {
        return $this->plan_id;
    }

    /**
     * @return mixed
     */
    public function getPlanName() {
        return $this->plan_name;
    }

    /**
     * @return mixed
     */
    public function getPlanValue() {
        return $this->plan_value;
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
     * @return mixed
     */
    public function getMaxListeners() {
        return $this->max_listeners;
    }

    /**
     * @return mixed
     */
    public function getMinTrackLength() {
        return $this->min_track_length;
    }

    /**
     * @return mixed
     */
    public function getPlanPeriod() {
        return $this->plan_period;
    }

} 