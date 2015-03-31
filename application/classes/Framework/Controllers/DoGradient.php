<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\FileServer\FSFile;
use Objects\Track;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

        $tracks = Track::getListByFilter("file_id IS NULL");

        foreach ($tracks as $track) {
            $filename = $track->getOriginalFile();
            $file_id = FSFile::registerLink($filename, $track->getHash());
            $track->setFileId($file_id);
            $track->save();
        }

    }
} 