<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Handlers;


use Framework\Controller;
use Framework\FileServer\FSFile;
use Objects\FileServer\FileServerFile;

class DoGradient implements Controller {
    public function doGet() {

        header("Content-Type: text/plain");
        set_time_limit(30);

        $files = FileServerFile::getListByFilter("use_count = 0");

        foreach ($files as $file) {
            FSFile::deleteLink($file->getFileId());
        }

        echo count($files);

    }

    public function doHead() {
        usleep(rand(500000, 5000000));
        http_response_code(404);
    }
} 