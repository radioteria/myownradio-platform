<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.12.14
 * Time: 17:05
 */

namespace Model;


use MVC\Services\Injectable;
use Tools\Singleton;

class Destructor extends Model {

    use Singleton, Injectable;

    public function deleteTrack($tracks) {
        foreach (explode(",", $tracks) as $track) {
            $object = new Track($track);
            $object->delete();
        }
    }

    public function deleteFromStreams($tracks) {

        $streams = $this->db->fetchAll("SELECT stream_id, GROUP_CONCAT(unique_id) as unique_ids
            FROM r_link WHERE FIND_IN_SET(track_id, ?) GROUP BY stream_id", [$tracks]);

        foreach($streams as $streamID => $uniqueIDs) {

            $stream = new StreamTrackList($streamID);
            $stream->removeTracks($uniqueIDs);

        }

    }

} 