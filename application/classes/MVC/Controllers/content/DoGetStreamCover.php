<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:01
 */

namespace MVC\Controllers\content;


use MVC\Controller;
use MVC\Exceptions\ControllerException;
use MVC\Services\HttpGet;
use MVC\Services\JsonResponse;
use Tools\File;
use Tools\Folders;

class DoGetStreamCover extends Controller {

    public function doGet(HttpGet $get, JsonResponse $response) {

        $response->disable();

        $fn = $get->getParameter("fn")->getOrElseThrow(ControllerException::noArgument("fn"));
        $size = $get->getParameter("size")->getOrElseNull();

        $folders = Folders::getInstance();

        $path = new File($folders->genStreamCoverPath($fn));

        if (in_array($path->extension(), array('jpg', 'png', 'gif')) === false) {
            header("HTTP/1.1 406 Not Acceptable");
            exit("HTTP/1.1 406 Not Acceptable");
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
            $image->output($path->extension(), 50);
        }

    }

} 