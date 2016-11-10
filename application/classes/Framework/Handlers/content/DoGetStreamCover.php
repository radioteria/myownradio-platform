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
        $s3 = S3::getInstance()->getS3Client();
        $fn = $get->getParameter("fn")->getOrElseThrow(new View404Exception());

        $size = $get->getParameter("size")->getOrElseNull();

        $path = 'covers/' . $fn;
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!$s3->doesObjectExist(config('services.s3.bucket'), $path)) {
            throw new View404Exception();
        }

        if ($size === null) {
            http_response_code(302);
            header("Location: " . $s3->getObjectUrl(config('services.s3.bucket'), $path));
            return;
        } else {
            $cachePath = $folders->generateCacheFile2($_GET, $extension);
            if (!$s3->doesObjectExist(config('services.s3.bucket'), $cachePath)) {
                $image = new \acResizeImage($s3->getObjectUrl(config('services.s3.bucket'), $path));
                $image->cropSquare();
                $image->resize($size);
                $image->interlace();

                ob_start();
                $image->output($extension, 80);
                $imageData = ob_get_clean();

                $s3->putObject([
                    'Bucket' => config('services.s3.bucket'),
                    'Key'    => $cachePath,
                    'Body'   => $imageData,
                    'ACL'    => 'public-read'
                ]);
            }
            http_response_code(302);
            header("Location: " . $s3->getObjectUrl(config('services.s3.bucket'), $cachePath));
            return;
        }
    }
}
