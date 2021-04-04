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
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;
use Tools\System;

class Playlist implements SingletonInterface, Injectable
{

    use Singleton;

    const NOW_PLAYING_TIME_RANGE = 1800000; // 15 minutes
    const REAL_TIME_DELAY_MS = 0;

    /**
     * @param Optional $color
     * @param Optional $filter
     * @param Optional $offset
     * @param int $sortRow
     * @param int $sortOrder
     * @return array
     */
    public function getAllTracks(Optional $color, Optional $filter, Optional $offset, $sortRow = 0, $sortOrder = 0)
    {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());

        $availableOrders = [0 => "DESC", 1 => "ASC"];
        $availableRows = [0 => "tid", 1 => "title", 2 => "artist", 3 => "genre", 4 => "duration"];

        $safeRow = isset($availableRows[$sortRow]) ? $sortRow : 0;
        $safeOrder = isset($availableOrders[$sortOrder]) ? $sortOrder : 0;

        if ($color->validate()) {
            $query->where("color", $color->get());
        }

        if ($filter->validate()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())
            ]);
        }

        if ($offset->validate()) {
            $query->offset($offset->get());
        }

        $query->limit(Defaults::DEFAULT_TRACKS_PER_REQUEST);

        $query->orderBy(sprintf("%s %s", $availableRows[$safeRow], $availableOrders[$safeOrder]));

        $this->printResults($query);

    }

    /**
     * @return SelectQuery
     */
    private function getTracksPrefix()
    {
        $query = DBQuery::getInstance()->selectFrom("r_tracks");
        $query->select("tid", "filename", "artist", "title", "duration",
            "color", "genre", "track_number", "album", "date", "cue", "is_new", "buy", "can_be_shared");
        return $query;
    }

    private function printResults(SelectQuery $query)
    {

        $printer = JsonPrinter::getInstance()->successPrefix();
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

    public function getUnusedTracks(Optional $color, Optional $filter, Optional $offset, $sortRow = 0, $sortOrder = 0)
    {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());

        $availableOrders = [0 => "DESC", 1 => "ASC"];
        $availableRows = [0 => "tid", 1 => "title", 2 => "artist", 3 => "genre", 4 => "duration"];

        $safeRow = isset($availableRows[$sortRow]) ? $sortRow : 0;
        $safeOrder = isset($availableOrders[$sortOrder]) ? $sortOrder : 0;

        if ($color->validate()) {
            $query->where("color", $color->get());
        }

        if ($filter->validate()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())
            ]);
        }

        if ($offset->validate()) {
            $query->offset($offset->get());
        }

        $query->where("used_count", 0);
        $query->limit(Defaults::DEFAULT_TRACKS_PER_REQUEST);

        $query->orderBy(sprintf("%s %s", $availableRows[$safeRow], $availableOrders[$safeOrder]));

        $this->printResults($query);

    }

    public function getOneTrack($trackID)
    {

        $me = AuthUserModel::getInstance();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());
        $query->where("tid", $trackID);

        return $query->fetchOneRow()->getOrElseThrow(ControllerException::noTrack($trackID));

    }

    /**
     * @param StreamModel $stream
     * @param Optional $color
     * @param Optional $filter
     * @param Optional $offset
     * @return array
     */
    public function getTracksByStream(StreamModel $stream, Optional $color, Optional $filter, Optional $offset)
    {

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getStreamTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        if ($color->validate()) {
            $query->where("color", $color);
        }

        if ($filter->validate()) {
            $query->where("MATCH(artist, title, genre) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter->get())
            ]);
        }

        if ($offset->validate()) {
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

    /**
     * @return SelectQuery
     */
    private function getStreamTracksPrefix()
    {
        $query = DBQuery::getInstance()->selectFrom("mor_stream_tracklist_view");
        $query->select("tid", "filename", "artist", "title", "duration", "color",
            "genre", "unique_id", "t_order", "track_number", "album", "date", "cue", "is_new", "buy", "can_be_shared");
        return $query;
    }

    public function getNowPlaying($id)
    {

        /** @var StreamStats $stream */

        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->getOrElseThrow(ControllerException::noStream($id));

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

        $track = $query->fetchOneRow()->getOrElseThrow(new ControllerException(sprintf("Nothing playing on stream '%s'",
            $id)));

        $track["caption"] = $track["artist"] . " - " . $track["title"];

        return [
            'time' => System::time(),
            'position' => $position,
            'current' => $track,
            'listeners_count' => $stream->getListenersCount(),
            'bookmarks_count' => $stream->getBookmarksCount()
        ];

    }

    /**
     * @param int $id
     * @return array
     * @throws ControllerException
     */
    public function getNowPlayingAndNext(int $id, int $prefetch_millis): array
    {
        /** @var StreamStats $stream */

        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->getOrElseThrow(ControllerException::noStream($id));

        if ($stream->getStatus() == 0) {
            throw ControllerException::noStream($id);
        }

        if ($stream->getTracksDuration() == 0) {
            throw ControllerException::of("Nothing playing");
        }

        $position = max(((System::time() - (self::REAL_TIME_DELAY_MS + $prefetch_millis)) -
                $stream->getStarted() +
                $stream->getStartedFrom()) % $stream->getTracksDuration(), 0);


        $query = $this->getStreamTracksPrefix();

        $query->select("time_offset");

        $query->where("time_offset + duration >= ?", [$position]);
        $query->where("stream_id", $stream->getID());

        $query->limit(2);

        $tracks = $query->fetchAll();

        if (count($tracks) === 0) {
            throw new ControllerException(sprintf("Nothing playing on stream '%s'", $id));
        }

        if (count($tracks) === 1) {
            list($track) = $query->fetchAll();
            $new_query = $this->getStreamTracksPrefix();
            $new_query->where("stream_id", $stream->getID());
            $next_track = $new_query->fetchOneRow()->get();
        } else {
            list($track, $next_track) = $query->fetchAll();
        }

        $track["caption"] = $track["artist"] . " - " . $track["title"];
        $next_track["caption"] = $next_track["artist"] . " - " . $next_track["title"];

        return [
            'time' => System::time(),
            'position' => $position,
            'current' => $track,
            'next' => $next_track,
            'listeners_count' => $stream->getListenersCount(),
            'bookmarks_count' => $stream->getBookmarksCount()
        ];
    }

    public function getSchedule($id)
    {

        /** @var StreamStats $stream */
        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->getOrElseThrow(ControllerException::noStream($id));

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
                if (!empty($row["artist"])) {
                    $row["caption"] = $row["artist"] . " - " . $row["title"];
                } else {
                    $row["caption"] = $row["title"];
                }
                $index++;
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