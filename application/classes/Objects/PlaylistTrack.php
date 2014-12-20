<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 10:47
 */

namespace Objects;
use Framework\Services\DB\DBQuery;

/**
 * Class PlaylistTrack
 * @package Objects
 * @table mor_stream_tracklist_view
 * @key unique_id
 * @view
 */
class PlaylistTrack extends Track {

    protected $id, $stream_id, $t_order, $unique_id, $time_offset;

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

    /**
     * @param $streamID
     * @return \Tools\Optional
     */
    public static function getRandom($streamID) {

        return DBQuery::getInstance()
            ->selectFrom("mor_stream_tracklist_view", "stream_id", $streamID)
            ->limit(1)->orderBy("RAND()")
            ->fetchObject("Objects\\PlaylistTrack");

    }

} 