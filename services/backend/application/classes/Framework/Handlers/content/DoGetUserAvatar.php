<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 17:32
 */

namespace Framework\Handlers\content;


use app\Services\Storage\StorageFactory;
use Framework\Controller;
use Framework\Services\HttpGet;
use Framework\View\Errors\View404Exception;
use Tools\Folders;

class DoGetUserAvatar implements Controller
{
    public function doGet(HttpGet $get, Folders $folders)
    {
        $storage = StorageFactory::getStorage();
        $fn = $get->getParameter("fn")->getOrElseThrow(new View404Exception());

        $size = $get->getParameter("size")->getOrElseNull();

        $path = 'avatars/' . $fn;
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        if (!$storage->exists($path)) {
            throw new View404Exception();
        }

        if ($size === null) {
            header("Location: " . $storage->url($path));
            return;
        } else {
            $cachePath = $folders->generateCacheFile2($_GET, $extension);
            if (!$storage->exists($cachePath)) {
                $image = new \acResizeImage($storage->url($path));
                $image->cropSquare();
                $image->resize($size);
                $image->interlace();

                ob_start();
                $image->output($extension, 80);
                $imageData = ob_get_clean();

                $storage->put($cachePath, $imageData, [
                    'ContentType' => mimetype_from_extension($extension)
                ]);
            }
            header("Location: " . $storage->url($cachePath));
            return;
        }
    }
}
