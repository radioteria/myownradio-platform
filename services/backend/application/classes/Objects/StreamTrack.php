<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 10:47
 */

namespace Objects;

use Framework\Exceptions\ControllerException;
use Framework\Services\DB\DBQuery;
use Tools\Optional;
use Tools\System;

/**
 * Class PlaylistTrack
 * @package Objects
 * @table mor_stream_tracklist_view
 * @key unique_id
 * @view
 */
class StreamTrack extends Track {

    protected $id, $stream_id, $t_order, $unique_id, $time_offset;

    function __construct() {
    }

    public static function getCurrent($streamID) {

        /** @var StreamStats $stream */
        $stream = StreamStats::getByID($streamID)
            ->getOrElseThrow(ControllerException::noStream($streamID));

        if ($stream->getStatus() == 0) {
            return Optional::noValue();
        }

        $streamPosition = (System::time() - $stream->getStarted() + $stream->getStartedFrom())
            % $stream->getTracksDuration();

        $track = self::getByFilter("time_offset <= :time AND time_offset + duration >= :time AND stream_id = :id", [
            ":time" => $streamPosition, ":id" => $streamID
        ]);

        return $track;

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