<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 17:57
 */

namespace Framework\Handlers\api\v2\streams;


use Framework\Controller;
use REST\Playlist;

class DoGetSchedule implements Controller {
    public function doGet($stream_id, Playlist $playlist) {
        return $playlist->getSchedule($stream_id);
    }
} 