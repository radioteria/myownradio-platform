<?php

/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.12.14
 * Time: 16:21
 */
class Streams extends Model {


    /**
     * @param int $from
     * @param int $limit
     * @return array
     */
    public static function getStreamList($from = 0, $limit = 50) {

        return self::getStreamListFiltered(null, null, $from, $limit);

    }

    /**
     * @param int $category
     * @param int $from
     * @param int $limit
     * @return array
     */
    public static function getStreamListCategory($category = null, $from = 0, $limit = 50) {

        return self::getStreamListFiltered(null, $category, $from, $limit);

    }

}