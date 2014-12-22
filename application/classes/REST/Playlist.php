<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:32
 */

namespace REST;


use Framework\Services\DB\DBQuery;
use Framework\Services\Injectable;
use Model\AuthUserModel;
use Model\StreamModel;
use Tools\Singleton;
use Tools\SingletonInterface;

class Playlist implements SingletonInterface, Injectable {

    use Singleton;

    private $user;

    function __construct() {
        $this->user = AuthUserModel::getInstance();
    }

    /**
     * @return \Framework\Services\DB\Query\SelectQuery
     */
    private function getTracksPrefix() {
        $query = DBQuery::getInstance()->selectFrom("mor_stream_tracklist_view");
        $query->select("tid", "filename", "artist", "title", "duration", "color");
        return $query;
    }

    /**
     * @return array
     */
    public function getAllTracks() {

        $query = $this->getTracksPrefix()
            ->where("uid", $this->user->getID());

        $tracks = $query->fetchAll();

        return $tracks;
    }

    /**
     * @param StreamModel $stream
     * @return array
     */
    public function getTracksByStream(StreamModel $stream) {

        $query = $this->getTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        $tracks = $query->fetchAll();

        return $tracks;

    }

} 