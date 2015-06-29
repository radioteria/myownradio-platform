<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11.01.15
 * Time: 12:02
 */

namespace Framework\Handlers\api\v2\tracks;


use Framework\Controller;
use REST\Playlist;

class DoGetTrackDetails implements Controller {
    public function doGet($track_id, Playlist $playlist) {
        return $playlist->getOneTrack($track_id);
    }
} 