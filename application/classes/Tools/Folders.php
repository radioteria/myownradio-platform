<?php

namespace Tools;

use Framework\Services\Injectable;
use Objects\Track;

class Folders {

    use Singleton, Injectable;

    /* Common constants */
    const MOR_REST_DOMAIN           = "http://myownradio.biz";
    const MOR_HEAP_FOLDER           = "/media/www/myownradio.biz/heap";
    const MOR_CONTENT_FOLDER        = "/media/www/myownradio.biz/content";

    /* Specific constants: URL */
    const MOR_URL_STREAM_COVERS     = "%s/content/streamcovers/%s";
    const MOR_URL_USER_AVATARS      = "%s/content/avatars/%s";

    /* Specific constants: PATH */
    const MOR_DIR_STREAM_COVERS     = "%s/streamcovers/%s";
    const MOR_DIR_USER_AVATARS      = "%s/avatars/%s";


    /* Url generators */
    function genStreamCoverUrl($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_URL_STREAM_COVERS, self::MOR_REST_DOMAIN, $filename);
    }

    function genAvatarUrl($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_URL_USER_AVATARS, self::MOR_REST_DOMAIN, $filename);
    }

    /* Dir generators */
    function genStreamCoverPath($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_DIR_STREAM_COVERS, self::MOR_HEAP_FOLDER, $filename);
    }

    /**
     * @param $filename
     * @return null|string
     */
    function genAvatarPath($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_DIR_USER_AVATARS, self::MOR_HEAP_FOLDER, $filename);
    }

    /**
     * @param Track $track
     * @return string
     */
    function getRealTrackPath(Track $track) {
        return sprintf("%s/a_%03d_original.%s",
            $this->getUserContentFolder($track->getUserID()),
            $track->getID(),
            $track->getExtension()
        );
    }

    /**
     * @param int $uid
     * @return string
     */
    function getUserContentFolder($uid) {
        return sprintf("%s/ui_%d", self::MOR_CONTENT_FOLDER, $uid);
    }

}