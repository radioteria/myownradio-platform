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
use Framework\Services\HttpGet;
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
            return $get->getParameter("limit", FILTER_VALIDATE_INT)->getOrElse($or);
        });
    }

    /**
     * @param $or
     * @return mixed
     */
    public function getOffset($or) {
        return Injector::run(function (HttpGet $get) use ($or) {
            return $get->getParameter("offset", FILTER_VALIDATE_INT)->getOrElse($or);
        });
    }

    /**
     * @return mixed
     */
    public function getChannelId() {
        return Injector::run(function (HttpGet $get) {
            return $get->getRequired("stream_id");
        });
    }

    /**
     * @return mixed
     */
    public function getTrackId() {
        return Injector::run(function (HttpGet $get) {
            return $get->getRequired("track_id");
        });
    }

} 