<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 10:47
 */

namespace Model\Beans;

/**
 * Class StreamTrackBean
 * @package Model\Beans
 */
class StreamTrackBean extends TrackBean {

    protected $unique_id, $t_order, $time_offset;

    protected $__time;

    function __construct($time) {
        $this->__time = $time;
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

    public function getCursor() {
        return $this->__time - $this->getTimeOffset();
    }

} 