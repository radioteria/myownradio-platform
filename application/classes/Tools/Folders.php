<?php

namespace Tools;

use Framework\Injector\Injectable;
use Objects\Track;

class Folders implements Injectable, SingletonInterface {

    use Singleton;

    /* Common constants */
    const MOR_REST_DOMAIN = "//myownradio.biz";
    const MOR_HEAP_FOLDER = "/media/www/myownradio.biz/heap";
    const MOR_CONTENT_FOLDER = "/media/www/myownradio.biz/content";

    /* Specific constants: URL */
    const MOR_URL_STREAM_COVERS = "%s/content/streamcovers/%s";
    const MOR_URL_USER_AVATARS = "%s/content/avatars/%s";

    /* Specific constants: PATH */
    const MOR_DIR_STREAM_COVERS = "%s/streamcovers/%s";
    const MOR_DIR_USER_AVATARS = "%s/avatars/%s";
    const MOR_DIR_CACHE_LOCATION = "%s/cache/%s/%s/%s.%s";

    /**
     * @param $data
     * @param File $file
     * @return File
     */
    function generateCacheFile($data, $file) {
        $data = serialize($data);
        $md5 = md5($data);
        return new File(sprintf(self::MOR_DIR_CACHE_LOCATION, self::MOR_HEAP_FOLDER,
            substr($md5, 0, 2), substr($md5, 2, 2), $md5, $file->extension()
        ));
    }

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
        $original = sprintf("%s/a_%03d_original.%s",
            $this->getUserContentFolder($track->getUserID()),
            $track->getID(),
            $track->getExtension()
        );
        $low = sprintf("%s/lores_%03d.mp3",
            $this->getUserContentFolder($track->getUserID()),
            $track->getID()
        );
        return file_exists($low) ? $low : $original;
    }

    /**
     * @param int $uid
     * @return string
     */
    function getUserContentFolder($uid) {
        return sprintf("%s/ui_%d", self::MOR_CONTENT_FOLDER, $uid);
    }

    function genStreamUrl($id) {
        return sprintf("http://myownradio.biz:7778/audio?s=%d", $id);
    }

    function genStreamPageUrl($id) {
        return sprintf("//myownradio.biz/streams/%s", $id["key"]);
    }


}