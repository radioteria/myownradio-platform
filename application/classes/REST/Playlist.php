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
use Tools\Singleton;
use Tools\SingletonInterface;
use Tools\System;

class Playlist implements SingletonInterface, Injectable {

    use Singleton;

    const NOW_PLAYING_TIME_RANGE = 900000; // 15 minutes

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

        $me = AuthUserModel::getInstance();

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getTracksPrefix()
            ->where("uid", $me->getID());

        if (is_numeric($color)) {
            $query->where("color", $color);
        }

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
     * @param StreamModel $stream
     * @param $color
     * @return array
     */
    public function getTracksByStream(StreamModel $stream, $color = null) {

        $printer = JsonPrinter::getInstance()->successPrefix();

        $query = $this->getTracksPrefix()
            ->where("stream_id", $stream->getID());

        $query->select("unique_id", "time_offset");

        if (is_numeric($color)) {
            $query->where("color", $color);
        }

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

        $stream = StreamStats::getByID($id)->getOrElseThrow(ControllerException::noStream($id));

        $position = (
                System::time() -
                $stream->getStarted() +
                $stream->getStartedFrom()) %
            $stream->getTracksDuration();

        $query = $this->getTracksPrefix();

        $lowRange = $position - self::NOW_PLAYING_TIME_RANGE;

        $highRange = $position + self::NOW_PLAYING_TIME_RANGE;

        $query->select("time_offset");

        $query->where("time_offset + duration > ?", [$lowRange]);
        $query->where("time_offset < ?", [$highRange]);
        $query->where("stream_id", $id);

        $tracks = $query->fetchAll();

        return [
            'percent' => number_format(100 / $stream->getTracksDuration() * $position),
            'time' => System::time(),
            'position' => $position,
            'range' => self::NOW_PLAYING_TIME_RANGE * 2,
            'tracks' => $tracks
        ];

    }

} 