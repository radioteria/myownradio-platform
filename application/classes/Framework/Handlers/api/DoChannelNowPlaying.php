<?php

namespace Framework\Handlers\api;

use app\Services\Storage\StorageFactory;
use Framework\Controller;
use Framework\FileServer\FSFile;
use Framework\Services\HttpGet;
use Framework\Services\JsonResponse;
use Framework\Services\RouteParams;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use REST\Playlist;

/**
 * Created by PhpStorm.
 * User: roman
 * Date: 3/23/17
 * Time: 4:08 PM
 */
class DoChannelNowPlaying implements Controller
{
    public function doGet(RouteParams $params, Playlist $playlist, JsonResponse $response)
    {
        $stream_id = $params->getRequired("stream_id");

        $now_playing = $playlist->getNowPlaying($stream_id);
        $track = $now_playing['current'];

        $track_position = $now_playing["position"] - $track["time_offset"];

        $track_object = Track::getByID($track["tid"])->get();

        $file = FileServerFile::getByID($track_object->getFileId())->get();

        $url = FSFile::getFileUrl($file);

        $response->setData([
            "offset" => $track_position,
            "title" => $track["caption"],
            "url" => $url
        ]);
    }
}