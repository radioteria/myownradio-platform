<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:32
 */

namespace REST;


use API\REST\TrackCollection;
use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Models\AuthUserModel;
use Framework\Models\StreamModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Objects\StreamStats;
use Tools\Common;
use Tools\JsonPrinter;
use Tools\Optional\Option;
use Tools\Singleton;
use Tools\SingletonInterface;
use Tools\System;

class Playlist implements SingletonInterface, Injectable {

    use Singleton;

    const NOW_PLAYING_TIME_RANGE = 1800000; // 15 minutes
    const REAL_TIME_DELAY_MS = 10000;

    /**
     * @param Option $color
     * @param Option $filter
     * @param Option $offset
     * @param int $sortRow
     * @param int $sortOrder
     * @return array
     * @throws ControllerException
     */
    public function getAllTracks(Option $color, Option $filter, Option $offset, $sortRow = 0, $sortOrder = 0) {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());

        $availableOrders    = [0 => "DESC", 1 => "ASC"];
        $availableRows      = [0 => "tid", 1 => "title", 2 => "artist", 3 => "genre", 4 => "duration"];

        $safeRow            = isset($availableRows[$sortRow]) ? $sortRow : 0;
        $safeOrder          = isset($availableOrders[$sortOrder]) ? $sortOrder : 0;

        if ($color->nonEmpty()) {
            $query->where("color", $color->get());
        }

        if ($filter->nonEmpty()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())]);
        }

        if ($offset->nonEmpty()) {
            $query->offset($offset->get());
        }

        $query->limit(Defaults::DEFAULT_TRACKS_PER_REQUEST);

        $query->orderBy(sprintf("%s %s", $availableRows[$safeRow], $availableOrders[$safeOrder]));

        $this->printResults($query);

    }

    public function getUnusedTracks(Option $color, Option $filter, Option $offset, $sortRow = 0, $sortOrder = 0) {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());

        $availableOrders    = [0 => "DESC", 1 => "ASC"];
        $availableRows      = [0 => "tid", 1 => "title", 2 => "artist", 3 => "genre", 4 => "duration"];

        $safeRow            = isset($availableRows[$sortRow]) ? $sortRow : 0;
        $safeOrder          = isset($availableOrders[$sortOrder]) ? $sortOrder : 0;

        if ($color->nonEmpty()) {
            $query->where("color", $color->get());
        }

        if ($filter->nonEmpty()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())]);
        }

        if ($offset->nonEmpty()) {
            $query->offset($offset->get());
        }

        $query->where("used_count", 0);
        $query->limit(Defaults::DEFAULT_TRACKS_PER_REQUEST);

        $query->orderBy(sprintf("%s %s", $availableRows[$safeRow], $availableOrders[$safeOrder]));

        $this->printResults($query);

    }

    private function printResults(SelectQuery $query) {

        $printer = JsonPrinter::getInstance()->successPrefix();
        $printer->brPrintKey("data");
        $printer->brOpenArray();

        $index = 0;

        $query->eachRow(function ($row) use (&$printer, &$index) {
            if ($index++ > 0) {
                $printer->brComma();
            }
            $printer->printJSON($row);
        });

        $printer->brCloseArray();

        $printer->brCloseObject();

    }

    public function getOneTrack($trackID) {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());
        $query->where("tid", $trackID);

        return $query->fetchOneRow()->orThrow(ControllerException::noTrack($trackID));

    }

    /**
     * @return \Framework\Services\DB\Query\SelectQuery
     */
    private function getStreamTracksPrefix() {
        $query = DBQuery::getInstance()->selectFrom("mor_stream_tracklist_view");
        $query->select("tid", "filename", "artist", "title", "duration", "color",
            "genre", "unique_id", "t_order", "track_number", "album", "date", "cue", "is_new", "buy", "can_be_shared");
        return $query;
    }

    /**
     * @return \Framework\Services\DB\Query\SelectQuery
     */
    private function getTracksPrefix() {
        $query = DBQuery::getInstance()->selectFrom("r_tracks");
        $query->select("tid", "filename", "artist", "title", "duration",
            "color", "genre", "track_number", "album", "date", "cue", "is_new", "buy", "can_be_shared");
        return $query;
    }

    /**
     * @param StreamModel $stream
     * @param Option $color
     * @param Option $filter
     * @param Option $offset
     * @return array
     */
    public function getTracksByStream(StreamModel $stream, Option $color, Option $filter, Option $offset) {

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getStreamTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        if ($color->nonEmpty()) {
            $query->where("color", $color);
        }

        if ($filter->nonEmpty()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())]);
        }

        if ($offset->nonEmpty()) {
            $query->offset($offset->get());
        }

        $query->limit(Defaults::DEFAULT_TRACKS_PER_REQUEST);

        $query->orderBy("time_offset ASC");

        $printer->brPrintKey("data");
        $printer->brOpenArray();

        $index = 0;

        $query->eachRow(function ($row) use ($printer, &$index) {
            if ($index++ > 0) {
                $printer->brComma();
            }
            $printer->printJSON($row);
        });

        $printer->brCloseArray();

        $printer->brCloseObject();

    }

    public function getNowPlaying($id) {

        /** @var StreamStats $stream */

        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->orThrow(ControllerException::noStream($id));

        if ($stream->getStatus() == 0) {
            throw ControllerException::noStream($id);
        }

        if ($stream->getTracksDuration() == 0) {
            throw ControllerException::of("Nothing playing");
        }

        $position = max(((System::time() - self::REAL_TIME_DELAY_MS) -
                $stream->getStarted() +
                $stream->getStartedFrom()) % $stream->getTracksDuration(), 0);


        $query = $this->getStreamTracksPrefix();

        $query->select("time_offset");

        $query->where("time_offset + duration >= ?", [$position]);
        $query->where("time_offset <= ?", [$position]);
        $query->where("stream_id", $stream->getID());

        $track = $query->fetchOneRow()->orThrow(new ControllerException(sprintf("Nothing playing on stream '%s'", $id)));

        $track["caption"] = $track["artist"] . " - " . $track["title"];

        return [
            'time' => System::time(),
            'position' => $position,
            'current' => $track,
            'listeners_count' => $stream->getListenersCount(),
            'bookmarks_count' => $stream->getBookmarksCount()
        ];

    }

    public function getSchedule($id) {

        /** @var StreamStats $stream */
        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->orThrow(ControllerException::noStream($id));

        if ($stream->getTracksDuration() == 0 || $stream->getStatus() == 0) {

            $position = 0;
            $tracks = [];
            $currentID = null;

        } else {

            $position = max(((System::time() - self::REAL_TIME_DELAY_MS) -
                    $stream->getStarted() +
                    $stream->getStartedFrom()) % $stream->getTracksDuration(), 0);

            $tracks = TrackCollection::getInstance()->getTimeLineOnChannel(
                $id,
                $position - (Defaults::TIMELINE_WIDTH >> 1),
                $position + (Defaults::TIMELINE_WIDTH >> 1)
            );

            $currentID = 0;
            $index = 0;
            foreach ($tracks as &$row) {
                if ($row["time_offset"] <= $position && $row["time_offset"] + $row["duration"] >= $position) {
                    $currentID = $index;
                }
                if (!empty($row["artist"]))
                    $row["caption"] = $row["artist"] . " - " . $row["title"];
                else
                    $row["caption"] = $row["title"];
                $index ++;
            }


        }

        return [
            'time' => System::time(),
            'position' => $position,
            'range' => Defaults::TIMELINE_WIDTH,
            'current' => $currentID,
            'tracks' => $tracks,
            'listeners_count' => $stream->getListenersCount(),
            'bookmarks_count' => $stream->getBookmarksCount()
        ];

    }

} 