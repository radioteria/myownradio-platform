<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 10:47
 */

namespace Model\Beans;

/**
 * Class StreamTrackAR
 * @package Model\Beans
 */
class StreamTrackAR extends TrackAR {

    protected $unique_id, $t_order, $time_offset;

    function __construct() {
    }

    public function getTrackOrder() {
        return $this->t_order;
    }

    public function getTimeOffset() {
        return $this->time_offset;
    }

    public function getUniqueID() {
        return $this->unique_id;
    }

} 