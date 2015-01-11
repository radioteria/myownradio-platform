<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 14:32
 */

namespace REST;


use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Models\StreamModel;
use Framework\Services\DB\DBQuery;
use Framework\Services\Injectable;
use Objects\StreamStats;
use Tools\JsonPrinter;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;
use Tools\System;

class Playlist implements SingletonInterface, Injectable {

    use Singleton;

    const NOW_PLAYING_TIME_RANGE = 900000; // 15 minutes
    const REAL_TIME_DELAY_MS = 10000;

    /**
     * @param Optional $color
     * @return array
     */
    public function getAllTracks(Optional $color) {

        $me = AuthUserModel::getInstance();

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getTracksPrefix()->where("uid", $me->getID());

        if ($color->validate()) {
            $query->where("color", $color->get());
        }

        $query->orderBy("tid DESC");

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
     * @return \Framework\Services\DB\Query\SelectQuery
     */
    private function getStreamTracksPrefix() {
        $query = DBQuery::getInstance()->selectFrom("mor_stream_tracklist_view");
        $query->select("tid", "filename", "artist", "title", "duration", "color");
        return $query;
    }

    /**
     * @return \Framework\Services\DB\Query\SelectQuery
     */
    private function getTracksPrefix() {
        $query = DBQuery::getInstance()->selectFrom("r_tracks");
        $query->select("tid", "filename", "artist", "title", "duration", "color");
        return $query;
    }

    /**
     * @param StreamModel $stream
     * @param $color
     * @return array
     */
    public function getTracksByStream(StreamModel $stream, $color = null) {

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getStreamTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        if (is_numeric($color)) {
            $query->where("color", $color);
        }

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
            ->getOrElseThrow(ControllerException::noStream($id));

        if ($stream->getStatus() == 0) {
            throw ControllerException::noStream($id);
        }

        $position = max(((System::time() - self::REAL_TIME_DELAY_MS) -
                $stream->getStarted() +
                $stream->getStartedFrom()) % $stream->getTracksDuration(), 0);


        $query = $this->getStreamTracksPrefix();

        $query->select("time_offset");

        $query->where("time_offset + duration >= ?", [$position]);
        $query->where("time_offset <= ?", [$position]);
        $query->where("stream_id", $stream->getID());

        $track = $query->fetchOneRow()->getOrElseThrow(new ControllerException(sprintf("Nothing playing on stream '%s'", $id)));

        $track["caption"] = $track["artist"] . " - " . $track["title"];

        return [
            'time' => System::time(),
            'position' => $position,
            'current' => $track,
        ];

    }

    public function getSchedule($id) {

        /** @var StreamStats $stream */

        $stream = StreamStats::getByFilter("sid = :id OR permalink = :id", [":id" => $id])
            ->getOrElseThrow(ControllerException::noStream($id));

        if ($stream->getTracksDuration() == 0) {

            $position = 0;
            $tracks = [];
            $currentID = null;

        }  else {

            $position = max(((System::time() - self::REAL_TIME_DELAY_MS) -
                    $stream->getStarted() +
                    $stream->getStartedFrom()) % $stream->getTracksDuration(), 0);

            $query = $this->getStreamTracksPrefix();

            $lowRange = $position - self::NOW_PLAYING_TIME_RANGE;

            $highRange = $position + self::NOW_PLAYING_TIME_RANGE;

            $query->select("time_offset");

            $query->where("time_offset + duration > ?", [$lowRange]);
            $query->where("time_offset <= ?", [$highRange]);
            $query->where("stream_id", $stream->getID());

            $currentID = 0;

            $tracks = $query->fetchAll(null, function ($row, $index) use (&$currentID, &$position) {
                if ($row["time_offset"] <= $position && $row["time_offset"] + $row["duration"] >= $position) {
                    $currentID = $index;
                }
                $row["caption"] = $row["artist"] . " - " . $row["title"];
                return $row;
            });

        }

        return [
            'time' => System::time(),
            'position' => $position,
            'range' => self::NOW_PLAYING_TIME_RANGE * 2,
            'current' => $currentID,
            'tracks' => $tracks
        ];

    }

} 