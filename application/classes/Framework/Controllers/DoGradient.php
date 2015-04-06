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
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\DB\Query\UpdateQuery;
use Framework\Services\Locale\L10n;
use Objects\FileServer\FileServerFile;
use Objects\Options;

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