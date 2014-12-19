<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 15.12.14
 * Time: 11:49
 */

namespace Model;

use Model\ActiveRecords\StreamAR;
use Model\ActiveRecords\StreamTrack;
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

    /** @var UserModel $user */
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

            if (intval($stats["uid"]) !== $this->user->getID()) {
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

                $trackObject = new TrackModel($track);
                //$uniqueId = $this->generateUniqueId($db);

                $query = $db->getDBQuery()->insertInto("r_link")
                    ->values([
                        "stream_id"     => $this->key,
                        "track_id"      => $trackObject->getID(),
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
     * @param $uniqueID
     * @param $index
     * @return $this
     */
    public function moveTrack($uniqueID, $index) {

        $this->doAtomic(function () use (&$uniqueID, &$index) {

            Database::doInConnection(function (Database $db) use (&$uniqueID, &$index) {
                $db->executeUpdate("SELECT NEW_STREAM_SORT(?, ?, ?)", [$this->key, $uniqueID, $index]);
            });

        });

        return $this;

    }

    /**
     * @param $id
     * @return Optional
     */
    public function getTrackByOrder($id) {

        return $this->_getPlaylistTrack("b.t_order = ? AND b.stream_id = ?", [$id, $this->key]);

    }

    /**
     * @param $time
     * @return \Model\ActiveRecords\StreamTrack
     */
    public function getTrackByTime($time) {

        return $this->_getPlaylistTrack(
            "b.time_offset <= :time AND b.time_offset + a.duration >= :time AND b.stream_id = :id",
            [":time" => $time, ":id" => $this->key]
        );

    }

    /**
     * @param callable $callable
     * @return $this
     */
    private function doAtomic(callable $callable) {

        $this->getStreamPosition()->then(function ($streamPosition) use ($callable) {
            /** @var Optional $streamPosition */
            $track = $this->getTrackByTime($streamPosition);
            $trackPosition = $streamPosition->get() - $track->getTimeOffset();
            call_user_func($callable);
            $this->_setCurrentTrack($track, $trackPosition, false);

        })->getOrElseCallback($callable);

        return $this;

    }

    private function _setCurrentTrack(StreamTrack $trackBean, $startFrom = 0, $notify = true) {

        StreamAR::getByID($this->key)
            ->then(function ($stream) use ($trackBean, $startFrom) {
                /** @var StreamAR $stream */
                $stream->setStartedFrom($trackBean->getTimeOffset() + $startFrom);
                $stream->setStarted(System::time());
                $stream->setStatus(1);
                $stream->save();
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

        $this->_getPlaylistTrack("b.unique_id = ? AND b.stream_id = ?", [$uniqueID, $this->key])
            ->then(function ($track) {
                $this->_setCurrentTrack($track, 0, true);
            })->justThrow(ControllerException::noTrack($uniqueID));


        return $this;

    }

    public function setRandomTrack() {

        $this->_getRandomTrack()
            ->then(function ($track) {
                $this->_setCurrentTrack($track, 0, true);
            });

    }


    public function generateUniqueId() {

        return Database::doInConnection(function (Database $conn) {

            do { $generated = Common::generateUniqueID(); }
            while ($conn->fetchOneColumn("SELECT COUNT(*) FROM r_link WHERE unique_id = ?", [$generated])->get());

            return $generated;

        });


    }

    /**
     * @param string $filter
     * @param array $args
     * @return Optional
     */
    private function _getPlaylistTrack($filter, array $args = null) {

        return Database::doInConnection(function (Database $db) use ($filter, $args) {

            $query = $this->getTrackQueryPrefix();
            $query->limit(1);
            $query->where($filter, $args);

            return $db->fetchOneObject($query, null, "Model\\ActiveRecords\\StreamTrackAR");

        });

    }

    /**
     * @return Optional
     */
    private function _getRandomTrack() {

        return Database::doInConnection(function (Database $db) {

            $query = $this->getTrackQueryPrefix();
            $query->addOrderBy("RAND()");
            $query->limit(1);
            $query->where("b.stream_id", $this->key);

            return $db->fetchOneObject($query, null, "Model\\ActiveRecords\\StreamTrackAR");

        });

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

    private function notifyStreamers() {
        self::notifyAllStreamers($this->key);
    }

    public static function notifyAllStreamers($streamID) {

        $ch = curl_init("http://127.0.0.1:7778/notify?s=" . $streamID);

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