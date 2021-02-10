<?php

namespace Framework\Handlers\api;

use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\JsonResponse;
use Framework\Services\RouteParams;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use REST\Playlist;
use REST\Streams;

/**
 * Created by PhpStorm.
 * User: roman
 * Date: 1/25/00
 * Time: 11:18 PM
 */
class DoChannelNowPlayingV1 implements Controller
{
    public function doGet(RouteParams $params, Playlist $playlist, Streams $streams, JsonResponse $response)
    {
        $stream_id = $params->getRequired("stream_id");
        $stream = $streams->getOneStream($stream_id);

        $now_playing = $playlist->getNowPlayingAndNext($stream_id);
        $track = $now_playing['current'];
        $next = $now_playing['next'];

        $track_position = $now_playing["position"] - $track["time_offset"];

        $current_track_object = Track::getByID($track["tid"])->get();
        $current_track_file = FileServerFile::getByID($current_track_object->getFileId())->get();
        $current_track_url = FSFile::getFileUrl($current_track_file);

        $next_track_object = Track::getByID($next["tid"])->get();
        $next_track_file = FileServerFile::getByID($next_track_object->getFileId())->get();
        $next_track_url = FSFile::getFileUrl($next_track_file);

        $response->setData([
            "playlist_position" => $track["t_order"],
            "current_track" => [
                "offset" => $track_position,
                "title" => $track["caption"],
                "url" => $this->replaceDomainWithCacheDomain($current_track_url),
            ],
            "next_track" => [
                "title" => $next["caption"],
                "url" => $this->replaceDomainWithCacheDomain($next_track_url),
            ],
            "image_url" => $stream['cover_url']
        ]);
    }

    private function replaceDomainWithCacheDomain($url)
    {
        return str_replace("//s3.eu-central-1.amazonaws.com", '//cache.myownradio.biz', $url);
    }
}
