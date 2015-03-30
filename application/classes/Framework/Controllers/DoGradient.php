<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\FileServer\FileServerFacade;
use Framework\FileServer\FSFile;
use Framework\Models\TrackModel;
use Objects\StreamTrack;
use Objects\Track;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

        $track = TrackModel::getInstance(927);

        echo $track->getFileUrl();

    }
} 