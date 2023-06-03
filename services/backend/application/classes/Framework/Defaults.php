<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 12.01.15
 * Time: 11:54
 */

namespace Framework;


class Defaults {

    const DEFAULT_TRACKS_PER_REQUEST = 50;
    const DEFAULT_STREAMS_PER_REQUEST = 20;

    const REDIS_ELEMENTS_KEY = "MOR2:Elements";
    const REDIS_OBJECTS_KEY = "MOR2:Objects";

    const SITE_TITLE = "Radioterio - Your own web radio station";

    const HASHING_ALGORITHM = "sha512";
    const SCHEDULE_TIME_SHIFT = 5000;
    const TIMELINE_WIDTH = 3600000;

    const NOTIFIER_URL = "http://localhost:8080/notif1er/notify";
    const NOTIFIER_TIMEOUT = 5;

    /**
     * @return array
     */
    public static function getStopWords() {
        return ["shit", "fuck", "ass", "хуй", "хуя", "пизда", "влагалище", "говно", "жопа", "писька"];
    }

}