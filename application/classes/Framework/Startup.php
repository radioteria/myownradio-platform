<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 16:40
 */

namespace Framework;


use Tools\File;

class Startup {
    public function start() {
        $path = new File("application/startup/");
        foreach ($path->getDirContents() as $file) {
            /** @var File $file */
            require_once $file->path();
        }
    }
}