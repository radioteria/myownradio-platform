<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Objects\Track;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(0);

        $tracks = Track::getListByFilter("LENGTH(hash) < 128");

        foreach ($tracks as $track) {
            echo $track->getFileName()."\n";
            $hash = hash("sha512", $track->getOriginalFile());
            $track->setHash($hash);
            $track->save();
            flush();
        }

    }
} 