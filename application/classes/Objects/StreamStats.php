<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.12.14
 * Time: 14:53
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class StreamStats
 * @package Objects
 * @table mor_stream_stats_view
 * @key sid
 * @view
 */
class StreamStats extends ActiveRecordObject implements ActiveRecord {
    private $sid, $uid, $started, $started_from, $status, $tracks_count, $tracks_duration;

    /**
     * @return mixed
     */
    public function getStarted() {
        return $this->started;
    }

    /**
     * @return mixed
     */
    public function getStartedFrom() {
        return $this->started_from;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getTracksCount() {
        return $this->tracks_count;
    }

    /**
     * @return mixed
     */
    public function getTracksDuration() {
        return $this->tracks_duration;
    }

    /**
     * @return mixed
     */
    public function getUserID() {
        return $this->uid;
    }

    /**
     * @return mixed
     */
    public function getID() {
        return $this->sid;
    }


} 