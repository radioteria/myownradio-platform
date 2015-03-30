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
use Objects\Track;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

//        $fs = new FileServerFacade(1);
//        echo $fs->isFileExists("c4e346f9c6e9b87dde72670505001b7d50fd8e6c8446e45f5659e868e02ec68e55e268dae27c651877963f5b0cc2689a0c707b2bed768590053ed13c805ba37b") ? "true" : "false";

        $tracks = Track::getListByFilter("file_id IS NULL");

        foreach ($tracks as $track) {
            error_log($track->getFileName());
            error_log($track->getID());
            $file_path = $track->getOriginalFile();
            $hash = hash_file("sha512", $file_path);
            error_log($hash);
            error_log("----");
            $file_id = FSFile::registerLink($file_path, $hash);
            $track->setHash($hash);
            $track->setFileId($file_id);
            $track->save();
        }

    }
} 