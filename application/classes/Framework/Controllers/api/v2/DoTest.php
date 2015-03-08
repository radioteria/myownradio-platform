<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 04.03.15
 * Time: 11:53
 */

namespace Framework\Controllers\api\v2;


use Framework\Controller;
use Objects\Stream;
use Tools\Folders;

class DoTest implements Controller {
    public function doGet() {
        header("Content-Type: text/plain");
        /** @var Stream $stream */

        $streams = Stream::getList();

        foreach ($streams as $stream) {
            if ($stream->getCover() === null) continue;
            $image = Folders::getInstance()->genStreamCoverPath($stream->getCover());
            $gd = new \acResizeImage($image);
            $color = $gd->getImageBackgroundColor();
            $stream->setCoverBackground($color);
            echo $color . "\n";
            $stream->save();
        }
    }
} 