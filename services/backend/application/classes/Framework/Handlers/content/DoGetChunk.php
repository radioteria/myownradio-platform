<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 28.02.18
 * Time: 9:51
 */

namespace Framework\Handlers\content;


use Framework\Controller;
use Framework\Services\HttpGet;
use REST\Playlist;
use Objects\Track;
use Framework\FileServer\FSFile;
use Objects\FileServer\FileServerFile;

class DoGetChunk implements Controller {
    public function doGet(HttpGet $get, Playlist $playlist) {
        $id = $get->getRequired('id');
        $now_playing = $playlist->getNowPlaying($id);

        $track = $now_playing['current'];
        $track_object = Track::getByID($track["tid"])->get();
        $file = FileServerFile::getByID($track_object->getFileId())->get();

        $position = ($now_playing["position"] - $track["time_offset"]) / 1000;
        $url = FSFile::getFileUrl($file);

        header("Content-Type: audio/mp3");
        set_time_limit(0);
        sendAsMp3Stream($url, $position);
    }
}
