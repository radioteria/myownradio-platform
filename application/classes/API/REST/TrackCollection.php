<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 13:27
 */

namespace API\REST;


use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Services\DB\Query\SelectQuery;
use Tools\Singleton;
use Tools\SingletonInterface;

class TrackCollection implements Injectable, SingletonInterface {
    use Singleton;

    /**
     * @return SelectQuery
     */
    private function getTracksPrefix() {

        $prefix = (new SelectQuery("r_tracks"))
            ->select("r_tracks.tid", "r_tracks.filename", "r_tracks.artist", "r_tracks.title", "r_tracks.album",
                "r_tracks.track_number", "r_tracks.genre", "r_tracks.date", "r_tracks.buy", "r_tracks.duration",
                "r_tracks.color", "r_tracks.can_be_shared");

        return $prefix;

    }

    /**
     * @return SelectQuery
     */
    private function getChannelQueuePrefix() {

        $prefix = $this->getTracksPrefix();
        $prefix->innerJoin("r_link", "r_link.track_id = r_tracks.tid");
        $prefix->select("r_link.t_order", "r_link.unique_id", "r_link.time_offset");

        return $prefix;

    }

    /**
     * @return SelectQuery
     */
    private function getSchedulePrefix() {

        $prefix = $this->getChannelQueuePrefix();
        $prefix->innerJoin("r_streams", "r_link.stream_id = r_streams.sid");
        $prefix->innerJoin("r_static_stream_vars", "r_streams.sid = r_static_stream_vars.stream_id");
        $prefix->where("r_link.time_offset <= MOD((UNIX_TIMESTAMP() * 1000) - (r_streams.started - r_streams.started_from), r_static_stream_vars.tracks_duration)");
        $prefix->where("r_link.time_offset + r_tracks.duration > MOD((UNIX_TIMESTAMP() * 1000) - (r_streams.started - r_streams.started_from), r_static_stream_vars.tracks_duration)");
        $prefix->where("r_streams.status = 1 AND r_static_stream_vars.tracks_duration > 0");
        $prefix->select("r_streams.sid");

        return $prefix;

    }

    /**
     * @param int $channel_id
     * @return mixed
     */
    public function getPlayingOnChannel($channel_id) {

        $query = $this->getSchedulePrefix();

        $query->where("r_streams.sid", $channel_id);

        return $query->fetchOneRow()->getOrElseThrow(ControllerException::of("NO_TRACK_PLAYING"));

    }

    public function getPlayingOnChannels(array $channel_ids) {

        $query = $this->getSchedulePrefix();
        $query->where("r_streams.sid", $channel_ids);

        return $query->fetchAll("sid");

    }

} 