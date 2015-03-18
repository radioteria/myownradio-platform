<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 17:32
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\DocNotFoundException;
use Framework\Services\HttpGet;
use Tools\File;
use Tools\Folders;

class DoGetUserAvatar implements Controller {

    public function doGet(HttpGet $get, Folders $folders) {

        $fn = $get->getParameter("fn")->getOrElseThrow(ControllerException::noArgument("fn"));
        $size = $get->getParameter("size")->getOrElseNull();

        $path = new File($folders->genAvatarPath($fn));

        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $path->mtime()) {
            header('HTTP/1.1 304 Not Modified');
            die();
        } else {
            header("Last-Modified: " . gmdate("D, d M Y H:i:s", $path->mtime()) . " GMT");
            header('Cache-Control: max-age=0');
        }

        if (!$path->exists()) {
            throw new DocNotFoundException();
        }

        header("Content-Type: " . $path->getContentType());
        header(sprintf('Content-Disposition: filename="%s"', $path->filename()));

        if ($size === null) {

            $path->echoContents();

        } else {


            $cache = $folders->generateCacheFile($_GET, $path);

            if ($cache->exists()) {

                $cache->echoContents();

            } else {

                if (!file_exists($cache->dirname())) {
                    mkdir($cache->dirname(), 0777, true);
                }

                $image = new \acResizeImage($path->path());
                $image->cropSquare();
                $image->resize($size);
                $image->interlace();

                $image->output($path->extension(), 80);
                $image->save($cache->dirname() . "/", $cache->filename(), $path->extension(), true, 80);

            }
        }

    }

} 