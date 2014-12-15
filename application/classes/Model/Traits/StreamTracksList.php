<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 18:33
 */

namespace Model\Traits;


use Model\Track;
use MVC\Exceptions\ControllerException;
use Tools\Common;
use Tools\Optional;
use Tools\System;

trait StreamTracksList {

    protected $tracksCount;
    protected $tracksDuration;

    public function addTracks($tracks) {

        return $this->doAtomic(function () use ($tracks) {

            $tracksToAdd = explode(",", $tracks);
            $initialPosition = $this->getTracksCount();
            $initialTimeOffset = $this->getTracksDuration();

            foreach($tracksToAdd as $track) {
                $trackObject = new Track($track);
                $uniqueId = $this->generateUniqueId();

                $this->db->queryInsert("INSERT INTO r_link VALUES (NULL, ?, ?, ?, ?, ?)",
                [$this->getSid(), $track, $initialPosition++, $uniqueId, $initialTimeOffset]);

                $initialTimeOffset += $trackObject->getDuration();
            }

        });

    }


    public function generateUniqueId() {

        do {

            $generated = Common::generateUniqueId();

        } while ($this->db->fetchOneColumn("SELECT COUNT(*) FROM r_link WHERE unique_id = ?", array($generated))
            ->getOrElseThrow(ControllerException::databaseError()) > 0);

        return $generated;

    }

    protected function reloadTrackListStats() {

        $stats = $this->db->fetchOneRow("SELECT * FROM r_static_stream_vars WHERE stream_id = ?", [$this->sid])
            ->getOrElseThrow(ControllerException::databaseError());

        $this->tracksCount = $stats['tracks_count'];
        $this->tracksDuration = $stats['tracks_duration'];

    }

    /**
     * @return mixed
     */
    public function getTracksCount() {
        return intval($this->tracksCount);
    }

    /**
     * @return mixed
     */
    public function getTracksDuration() {
        return intval($this->tracksDuration);
    }



} 