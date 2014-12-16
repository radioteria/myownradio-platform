<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 11:49
 */

namespace Model;

use MVC\Exceptions\ControllerException;
use Tools\Common;
use Tools\Optional;
use Tools\Singleton;
use Tools\System;

class StreamTrackList extends Model {

    use Singleton;

    private $key;
    private $user;

    private $tracks_count;
    private $tracks_duration;

    private $status;
    private $started_from;
    private $started;

    public function __construct($id) {
        parent::__construct();
        $this->user = AuthorizedUser::getInstance();
        $this->key = $id;
        $this->reload();
    }

    /**
     * @throws ControllerException
     * @return $this
     */
    public function reload() {

        $stats = $this->db->fetchOneRow("SELECT a.uid,a.started, a.started_from, a.status, b.tracks_count, b.tracks_duration
            FROM r_streams a LEFT JOIN r_static_stream_vars b on a.sid = b.stream_id WHERE a.sid = ?",
            [$this->key])->getOrElseThrow(ControllerException::noStream($this->key));

        if (intval($stats["uid"]) !== $this->user->getId()) {
            throw ControllerException::noPermission();
        }

        $this->tracks_count = intval($stats["tracks_count"]);
        $this->tracks_duration = intval($stats["tracks_duration"]);

        $this->status = intval($stats["status"]);
        $this->started = intval($stats["started"]);
        $this->started_from = intval($stats["started_from"]);

        return $this;

    }

    /**
     * @return Optional
     */
    public function getStreamPosition() {

        if ($this->tracks_duration == 0) {
            return Optional::ofNull(0);
        }

        if ($this->status == 0) {
            return Optional::ofNull(null);
        }

        $time = System::time();

        $position = ($time - $this->started + $this->started_from) % $this->tracks_duration;

        return Optional::ofNull($position);

    }

    /**
     * @return mixed
     */
    public function getTrackInStream() {
        return $this->tracks_count;
    }

    /**
     * @return mixed
     */
    public function getStreamDuration() {
        return $this->tracks_duration;
    }

    /**
     * @param $tracks
     * @return $this
     */
    public function addTracks($tracks) {

        $this->doAtomic(function () use (&$tracks) {

            $tracksToAdd = explode(",", $tracks);
            $initialPosition = $this->tracks_count;
            $initialTimeOffset = $this->tracks_duration;

            foreach($tracksToAdd as $track) {

                $trackObject = new Track($track);
                $uniqueId = $this->generateUniqueId();

                $this->db->executeInsert("INSERT INTO r_link VALUES (NULL, ?, ?, ?, ?, ?)",
                    [$this->key, $trackObject->getId(), ++$initialPosition, $uniqueId, $initialTimeOffset]);

                $initialTimeOffset += $trackObject->getDuration();

            }

        });

        return $this;

    }

    /**
     * @param $tracks
     * @return $this
     */
    public function removeTracks($tracks) {

        $this->doAtomic(function () use (&$tracks) {

            $this->db->executeUpdate("DELETE FROM r_link WHERE FIND_IN_SET(unique_id, ?)", [$tracks]);
            $this->db->executeUpdate("CALL POptimizeStream(?)", [$this->key]);

        });

        return $this;

    }

    /**
     * @return $this
     */
    public function shuffleTracks() {

        $this->doAtomic(function () {

            $this->db->executeUpdate("CALL PShuffleStream(?)", [$this->key]);

        });

        return $this;

    }

    /**
     * @param $unique
     * @param $index
     * @return $this
     */
    public function moveTrack($unique, $index) {

        $this->doAtomic(function () use ($unique, $index) {
            $this->db->executeUpdate("SELECT NEW_STREAM_SORT(?, ?, ?)", [$this->key, $unique, $index]);
        });

        return $this;

    }

    /**
     * @return Optional
     */
    public function getCurrentTrack() {

        $time = $this->getStreamPosition();


        if ($time->validate()) {
            return $this->getTrackByTime($time->getRaw());
        }

        return Optional::bad();

    }

    /**
     * @param $time
     * @return $this
     */
    public function getTrackByTime($time) {

        $track = $this->db->fetchOneRow("

            SELECT a.*, b.unique_id, b.t_order, b.time_offset
            FROM r_tracks a LEFT JOIN r_link b ON a.tid = b.track_id
            WHERE b.time_offset <= :time AND b.time_offset + a.duration >= :time AND b.stream_id = :id

            ", [":time" => $time, ":id" => $this->key])

            ->then(function (&$track) use ($time) {
                $track['cursor'] = $time - $track['time_offset'];
            });

        return $track;

    }

    /**
     * @param callable $callable
     * @return $this
     */
    private function doAtomic(callable $callable) {

        $track = $this->getCurrentTrack();

        call_user_func($callable);

        $this->setCurrentTrack($track);

        return $this;

    }

    /**
     * @param Optional $track
     * @param bool $force
     * @return $this
     */
    private function setCurrentTrack(Optional $track, $force = false) {

        $track->then(function ($track) {

            $query = "SELECT time_offset FROM r_link WHERE unique_id = ? AND stream_id = ?";

            $this->db->fetchOneColumn($query, [$track["unique_id"], $this->key])
                ->then(function ($offset) use ($track) {

                    $cursor = $track["cursor"];
                    $query = "UPDATE r_streams SET started_from = :from, started = :time, status = 1 WHERE sid = :id";

                    $this->db->executeUpdate($query, [
                        ":id" => $this->key,
                        ":time" => System::time(),
                        ":from" => $offset + $cursor]);

                });

                // TODO: Otherwise method needs to be implemented

        });

        if ($force) {
            $this->notifyStreamers();
        }

        return $this;

    }

    /**
     * @param $track
     * @return $this
     */
    public function setPlayFrom($track) {

        $query = $this->db->getFluentPDO()->from("r_tracks a")->
            leftJoin("r_link b ON a.tid = b.track_id")
            ->where("b.unique_id", $track)
            ->where("b.stream_id", $this->key)
            ->select(["b.unique_id", "b.time_offset"]);

        $track = $this->db->fetchOneRow($query->getQuery(false), $query->getParameters())
            ->then(function (&$track) { $track["cursor"] = 0; });

        $track->justThrow(ControllerException::noTrack($track));

        $this->setCurrentTrack($track, true);

        return $this;

    }


    public function generateUniqueId() {

        do {

            $generated = Common::generateUniqueId();

        } while ($this->db->fetchOneColumn("SELECT COUNT(*) FROM r_link WHERE unique_id = ?", [$generated])->getRaw());

        return $generated;

    }

    private function notifyStreamers() {
        self::notifyAllStreamers($this->key);
    }

    public static function notifyAllStreamers($streamId) {
        $ch = curl_init('http://127.0.0.1:7778/notify?s=' . $streamId);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);
        curl_close($ch);
    }

} 