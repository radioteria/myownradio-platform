<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:01
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\DocNotFoundException;
use Framework\Services\HttpGet;
use Tools\File;
use Tools\Folders;

class DoGetStreamCover implements Controller {

    public function doGet(HttpGet $get) {

        $fn = $get->getParameter("fn")->getOrElseThrow(ControllerException::noArgument("fn"));
        $size = $get->getParameter("size")->getOrElseNull();

        $folders = Folders::getInstance();

        $path = new File($folders->genStreamCoverPath($fn));

        if (!$path->exists()) {
            throw new DocNotFoundException();
        }

        header("Content-Type: " . $path->getContentType());
        header(sprintf('Content-Disposition: filename="%s"', $path->filename()));

        if ($size === null) {

            $path->echoContents();

        } else {
            $image = new \acResizeImage($path->path());
            $image->cropSquare();
            $image->resize($size);
            $image->interlace();
            $image->output($path->extension(), 100);
        }

    }

} 