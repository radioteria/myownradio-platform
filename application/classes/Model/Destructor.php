<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 16.12.14
 * Time: 17:05
 */

namespace Model;


use MVC\Services\Database;
use MVC\Services\Injectable;
use Tools\Singleton;

class Destructor extends Model {

    use Singleton, Injectable;

    public function deleteTrack($tracks) {

        foreach (explode(",", $tracks) as $track) {
            (new TrackModel($track))->delete();
        }

    }

    public function deleteFromStreams($tracks) {

        $streams = Database::doInConnection(function (Database $db) use ($tracks) {

            $query = $db->getDBQuery()
                ->selectFrom("r_link")
                ->select("stream_id")
                ->selectAlias("GROUP_CONCAT(unique_id)", "unique_ids")
                ->where("FIND_IN_SET(track_id, ?)", $tracks)
                ->addGroupBy("stream_id");

            return $db->fetchAll($query);

        });

        foreach($streams as $streamID => $uniqueIDs) {

            (new StreamTrackListModel($streamID))
                ->removeTracks($uniqueIDs);

        }

    }

} 