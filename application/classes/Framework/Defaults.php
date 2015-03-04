<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 12.01.15
 * Time: 11:54
 */

namespace Framework;


class Defaults {
    const DEFAULT_TRACKS_PER_REQUEST = 100;
    const DEFAULT_STREAMS_PER_REQUEST = 20;

    const REDIS_ELEMENTS_KEY = "MOR2:Elements";
    const REDIS_OBJECTS_KEY = "MOR2:Objects";
}