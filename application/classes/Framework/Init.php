<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 27.06.15
 * Time: 16:40
 */

namespace Framework;


use Tools\File;

class Init {
    public function init() {
        $path = new File("application/init/");
        foreach ($path->getDirContents() as $file) {
            /** @var File $file */
            require_once $file->path();
        }
    }
}