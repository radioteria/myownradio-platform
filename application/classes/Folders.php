<?php

class Folders {

    /* Common constants */
    const MOR_REST_DOMAIN           = "http://myownradio.biz";
    const MOR_CONTENT_PATH          = "/media/www/myownradio.biz/heap";

    /* Specific constants: URL */
    const MOR_URL_STREAM_COVERS     = "%s/content/streamcovers/%s";
    const MOR_URL_USER_AVATARS      = "%s/content/avatars/%s";

    /* Specific constants: PATH */
    const MOR_DIR_STREAM_COVERS     = "%s/streamcovers/%s";
    const MOR_DIR_USER_AVATARS      = "%s/avatars/%s";


    /* Url generators */
    static function genStreamCoverUrl($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_URL_STREAM_COVERS, self::MOR_REST_DOMAIN, $filename);
    }

    static function genAvatarUrl($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_URL_USER_AVATARS, self::MOR_REST_DOMAIN, $filename);
    }

    /* Dir generators */
    static function genStreamCoverPath($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_DIR_STREAM_COVERS, self::MOR_CONTENT_PATH, $filename);
    }

    static function genAvatarPath($filename) {
        if ($filename === null) return null;
        return sprintf(self::MOR_DIR_USER_AVATARS, self::MOR_CONTENT_PATH, $filename);
    }

}