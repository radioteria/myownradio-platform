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



    /**
     * @param $id
     * @return array
     */
    public static function getSimilarTo($id) {

        $involved_users = [];

        $db = Database::getInstance();

        $fluent = self::getStreamsPrefix();
        $fluent->where("a.sid != :id");
        $fluent->where("a.permalink != :id");
        $fluent->where("MATCH(a.hashtags) AGAINST((SELECT hashtags FROM r_streams WHERE (sid = :id) OR
                                                            (permalink = :id AND permalink != '')))", [':id' => $id]);
        $fluent->limit(self::MAXIMUM_SIMILAR_COUNT);

        $streams = $db->fetchAll($fluent->getQuery(false), $fluent->getParameters(), null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            self::processStreamRow($row);
            return $row;
        });

        $users = self::getUsersList($db, $involved_users);

        return ['streams' => $streams, 'users' => $users];

    }


}