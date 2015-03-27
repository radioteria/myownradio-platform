<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 26.03.15
 * Time: 17:34
 */

namespace Framework\Controllers;


use Framework\Controller;
use Framework\Services\HttpGet;
use Objects\Stream;
use Objects\User;
use REST\Users;
use Tools\Common;
use Tools\Folders;

class DoGradient implements Controller {
    public function doGet() {

        $streams = Stream::getListByFilter("cover IS NULL");

        foreach ($streams as $stream) {
            // Generate Stream Cover
            $random = Common::generateUniqueID();
            $newImageFile = sprintf("stream%05d_%s.%s", $stream->getID(), $random, "png");
            $newImagePath = Folders::getInstance()->genStreamCoverPath($newImageFile);

            Common::createTemporaryImage($newImagePath);

            $stream->setCover($newImageFile);
            $stream->save();
        }

        $users = User::getListByFilter("avatar IS NULL");

        foreach ($users as $user) {
            // Generate Stream Cover
            $random = Common::generateUniqueID();
            $newImageFile = sprintf("avatar%05d_%s.%s", $user->getID(), $random, "png");
            $newImagePath = Folders::getInstance()->genAvatarPath($newImageFile);

            Common::createTemporaryImage($newImagePath);

            $user->setAvatar($newImageFile);
            $user->save();
        }

//        header("Content-Type: image/png");

//        Common::createTemporaryImage();

    }
} 