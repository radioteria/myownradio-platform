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
     * @param null $color
     * @return array
     */
    public function getAllTracks($color = null) {

        $query = $this->getTracksPrefix()
            ->where("uid", $this->user->getID());

        if (is_numeric($color)) {
            $query->where("color", $color);
        }

        $tracks = $query->fetchAll();

        return $tracks;
    }

    /**
     * @param StreamModel $stream
     * @param $color
     * @return array
     */
    public function getTracksByStream(StreamModel $stream, $color = null) {

        $query = $this->getTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        if (is_numeric($color)) {
            $query->where("color", $color);
        }

        $tracks = $query->fetchAll();

        return $tracks;

    }

} 