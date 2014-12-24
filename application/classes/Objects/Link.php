<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:05
 */

namespace Objects;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;


/**
 * Class Link
 * @package Model\ActiveRecords
 *
 * @table r_link
 * @key id
 */
class Link extends ActiveRecordObject implements ActiveRecord {

    protected $id, $stream_id, $track_id, $t_order, $unique_id, $time_offset;

    /**
     * @return mixed
     */
    public function getID() {
        return $this->id;
    }

    /**
     * @param mixed $stream_id
     */
    public function setStreamID($stream_id) {
        $this->stream_id = $stream_id;
    }

    /**
     * @return mixed
     */
    public function getStreamID() {
        return $this->stream_id;
    }

    /**
     * @param mixed $t_order
     */
    public function setTrackOrder($t_order) {
        $this->t_order = $t_order;
    }

    /**
     * @return mixed
     */
    public function getTrackOrder() {
        return $this->t_order;
    }

    /**
     * @param mixed $time_offset
     */
    public function setTimeOffset($time_offset) {
        $this->time_offset = $time_offset;
    }

    /**
     * @return mixed
     */
    public function getTimeOffset() {
        return $this->time_offset;
    }

    /**
     * @param mixed $track_id
     */
    public function setTrackID($track_id) {
        $this->track_id = $track_id;
    }

    /**
     * @return mixed
     */
    public function getTrackID() {
        return $this->track_id;
    }

    /**
     * @param mixed $unique_id
     */
    public function setUniqueID($unique_id) {
        $this->unique_id = $unique_id;
    }

    /**
     * @return mixed
     */
    public function getUniqueID() {
        return $this->unique_id;
    }


} 