<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 16:01
 */

namespace Framework\Handlers\content;

use app\Providers\S3;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\View\Errors\View404Exception;
use Tools\Folders;

class DoGetStreamCover implements Controller
{
    public function doGet(HttpGet $get, Folders $folders)
    {
        $s3 = S3::getInstance();
        $fn = $get->getParameter("fn")->getOrElseThrow(new View404Exception());

        $size = $get->getParameter("size")->getOrElseNull();

        $path = 'covers/' . $fn;
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!$s3->doesObjectExist($path)) {
            throw new View404Exception();
        }

        if ($size === null) {
            header("Location: " . $s3->url($path));
            return;
        } else {
            $cachePath = $folders->generateCacheFile2($_GET, $extension);
            if (!$s3->doesObjectExist($cachePath)) {
                $image = new \acResizeImage($s3->url($path));
                $image->cropSquare();
                $image->resize($size);
                $image->interlace();

                ob_start();
                $image->output($extension, 80);
                $imageData = ob_get_clean();

                $s3->put($cachePath, $imageData, mimetype_from_extension($extension));
            }
            header("Location: " . $s3->url($cachePath));
            return;
        }
    }
}
