<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.12.14
 * Time: 16:26
 */

namespace Framework;


use Framework\Injector\Injectable;
use Framework\Injector\Injector;
use Framework\Services\Http\HttpGet;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class Context
 * @package Framework
 */
class Context implements SingletonInterface, Injectable {

    use Singleton;

    /**
     * @param $or
     * @return mixed
     */
    public function getLimit($or) {
        return Injector::run(function (HttpGet $get) use ($or) {
            return $get->get("limit", FILTER_VALIDATE_INT)->getOrElse($or);
        });
    }

    /**
     * @param $or
     * @return mixed
     */
    public function getOffset($or) {
        return Injector::run(function (HttpGet $get) use ($or) {
            return $get->get("offset", FILTER_VALIDATE_INT)->getOrElse($or);
        });
    }

    /**
     * @return mixed
     */
    public function getChannelId() {
        return Injector::run(function (HttpGet $get) {
            return $get->getOrError("stream_id");
        });
    }

    /**
     * @return mixed
     */
    public function getTrackId() {
        return Injector::run(function (HttpGet $get) {
            return $get->getOrError("track_id");
        });
    }

} 