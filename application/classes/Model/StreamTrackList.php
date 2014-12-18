<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 15.12.14
 * Time: 11:49
 */

namespace Model;

use Model\ActiveRecords\StreamTrackAR;
use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
use MVC\Services\DB\DBQuery;
use MVC\Services\DB\DBQueryPool;
use Tools\Common;
use Tools\Optional;
use Tools\Singleton;
use Tools\System;

class StreamTrackList extends Model implements \Countable {

    use Singleton;

    private $key;

    /** @var User $user */
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

        Database::doInConnection(function (Database $db) {

            $query = $db->getDBQuery()->selectFrom("r_streams a")
                ->innerJoin("r_static_stream_vars b", "a.sid = b.stream_id")
                ->where("a.sid", $this->key)
                ->select("a.uid, a.started, a.started_from, a.status, b.tracks_count, b.tracks_duration");

            $stats = $db->fetchOneRow($query)
                ->getOrElseThrow(ControllerException::noStream($this->key));

            if (intval($stats["uid"]) !== $this->user->getId()) {
                throw ControllerException::noPermission();
            }

            $this->tracks_count = intval($stats["tracks_count"]);
            $this->tracks_duration = intval($stats["tracks_duration"]);

            $this->status = intval($stats["status"]);
            $this->started = intval($stats["started"]);
            $this->started_from = intval($stats["started_from"]);

        });

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

            $pool = new DBQueryPool();

            foreach($tracksToAdd as $track) {

                $trackObject = new Track($track);
                //$uniqueId = $this->generateUniqueId($db);

                $query = $db->getDBQuery()->insertInto("r_link")
                    ->values([
                        "stream_id"     => $this->key,
                        "track_id"      => $trackObject->getId(),
                        "t_order"       => ++$initialPosition,
                        "unique_id"     => $uniqueId,
                        "time_offset"   => $initialTimeOffset
                    ]);

                $db->executeInsert($query);

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

            Database::doInConnection(function (Database $db) use ($tracks) {
                $db->executeUpdate("DELETE FROM r_link WHERE FIND_IN_SET(unique_id, ?)", [$tracks]);
                $db->executeUpdate("CALL POptimizeStream(?)", [$this->key]);
                $db->commit();
            });

        });

        return $this;

    }

    /**
     * @return $this
     */
    public function shuffleTracks() {

        $this->doAtomic(function () {

            Database::doInConnection(function (Database $db) {
                $db->executeUpdate("CALL PShuffleStream(?)", [$this->key]);
                $db->commit();
            });

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

            Database::doInConnection(function (Database $db) use ($unique, $index) {
                $db->executeUpdate("SELECT NEW_STREAM_SORT(?, ?, ?)", [$this->key, $unique, $index]);
                $db->commit();
            });

        });

        return $this;

    }

    /**
     * @return \MVC\Services\DB\Query\SelectQuery
     */
    private function getTrackQueryPrefix() {

        $query = DBQuery::getInstance()->selectFrom("r_tracks a")
            ->innerJoin("r_link b", "a.tid = b.track_id");

        $query->select("a.*, b.unique_id, b.t_order, b.time_offset");

        return $query;

    }

    /**
     * @return Optional
     */
    public function getCurrentTrack() {

        $time = $this->getStreamPosition();

        if ($time->validate()) {
            return $this->getTrackByTime($time->getRaw());
        }

        return Optional::noValue();

    }

    /**
     * @param $id
     * @return Optional
     */
    public function getTrackByOrder($id) {

        return Database::doInConnection(function (Database $db) use ($id) {

            $query = $this->getTrackQueryPrefix();
            $query->where("b.t_order", $id);
            $query->where("b.stream_id", $this->key);

            $trackObject = $db->fetchOneObject($query, null, "Model\\ActiveRecords\\StreamTrackAR", null);

            return Optional::ofNull($trackObject->getOrElseNull());

        });

    }

    /**
     * @param $time
     * @return \Model\ActiveRecords\StreamTrackAR
     */
    public function getTrackByTime($time) {

        Database::doInConnection(function (Database $db) use ($time) {

            $query = $this->getTrackQueryPrefix();
            $query->where("b.time_offset <= :time");
            $query->where("b.time_offset + a.duration >= :time", [":time" => $time]);
            $query->where("b.stream_id", $this->key);

            $track = $db->fetchOneObject($query, [], "Model\\ActiveRecords\\StreamTrackAR", [$time]);

            return $track;

        });

    }

    /**
     * @param callable $callable
     * @return $this
     */
    private function doAtomic(callable $callable) {

        $time = $this->getStreamPosition();
        $track = $this->getCurrentTrack();

        call_user_func($callable);

        $this->setCurrentTrack($track);

        return $this;

    }

    private function restorePlayingTrack(Optional $track, Optional $time) {

    }

    /**
     * @param Optional $track
     * @param bool $force
     * @return $this
     */
    private function setCurrentTrack(Optional $track, $force = false) {

        $track->then(function ($track) {

            /** @var \Model\ActiveRecords\StreamTrackAR $track */

            Database::doInConnection(function (Database $db) use ($track) {

                $query = "SELECT time_offset FROM r_link WHERE unique_id = ? AND stream_id = ?";

                $db->fetchOneColumn($query, [$track->getUniqueID(), $this->key])

                    ->then(function ($offset) use ($track, $db) {

                        $cursor = $track->getCursor();
                        $query = $db->getDBQuery()->updateTable("r_streams")
                            ->set("started_from", $offset + $cursor)
                            ->set("started", System::time())
                            ->set("status", 1)
                            ->where("sid", $this->key);

                        $db->executeUpdate($query);

                    })->otherwise(function () use ($track) {

                        $order = $track->getTrackOrder();

                    });

                $db->commit();

            });

        });

        if ($force == true) {
            $this->notifyStreamers();
        }

        return $this;

    }

    private function _setCurrentTrack(StreamTrackAR $trackBean, $startFrom = 0, $notify = true) {

        Database::doInConnection(function (Database $db) use ($trackBean, $startFrom) {
            $query = $db->getDBQuery()->updateTable("r_streams")
                ->set("started_from", $trackBean->getTimeOffset() + $startFrom)
                ->set("started", System::time())
                ->set("status", 1)
                ->where("sid", $this->key);
            $db->executeUpdate($query);
        });

        if ($notify == true) {
            $this->notifyStreamers();
        }

    }

    /**
     * @param $uniqueID
     * @return $this
     */
    public function setPlayFrom($uniqueID) {

        $track = Database::doInConnection(function (Database $db) use ($uniqueID) {

            $query = $db->getDBQuery()->selectFrom("r_tracks a")
                ->innerJoin("r_link b", "a.tid = b.track_id")
                ->where("b.unique_id", $uniqueID)
                ->select("b.unique_id", "b.time_offset");

            $track = $db->fetchOneRow($query)
                ->then(function (&$track) { $track["cursor"] = 0; });

            $track->justThrow(ControllerException::noTrack($uniqueID));

            return $track;

        });

        $this->setCurrentTrack($track, true);

        return $this;

    }


    public function generateUniqueId(Database $connection) {

            do { $generated = Common::generateUniqueId(); }
            while ($connection->fetchOneColumn("SELECT COUNT(*) FROM r_link WHERE unique_id = ?", [$generated])->getRaw());

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

    /**
     * @return int
     */
    public function count() {
        return intval($this->tracks_count);
    }



} 