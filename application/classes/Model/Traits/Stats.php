<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 16.12.14
 * Time: 20:26
 */

namespace Model\Traits;


use Framework\Exceptions\ApplicationException;
use Framework\Services\Database;

trait Stats {
    protected $tracks_count;
    protected $tracks_duration;
    protected $tracks_size;
    protected $streams_count;

    protected function loadStats() {

        Database::doInConnection(function (Database $db) {

            $stats = $db->fetchOneRow("SELECT * FROM r_static_user_vars WHERE user_id = ?", [$this->getID()])
                ->getOrElseThrow(ApplicationException::of("NO_STATIC_USER_VARS"));

            $this->tracks_count = $stats["tracks_count"];
            $this->tracks_duration = $stats["tracks_duration"];
            $this->tracks_size = $stats["tracks_size"];
            $this->streams_count = $stats["streams_count"];

        });

    }

    /**
     * @return mixed
     */
    public function getStreamsCount() {
        return $this->streams_count;
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
    public function getTracksSize() {
        return $this->tracks_size;
    }


} 