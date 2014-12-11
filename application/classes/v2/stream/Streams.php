<?php

/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.12.14
 * Time: 16:21
 */
class Streams extends Model {

    const STREAM_FETCH_LIST     = "SELECT a.sid, a.uid, a.name, a.permalink, a.info, a.hashtags, a.cover, a.created, b.bookmarks_count, b.listeners_count
                                   FROM r_streams a LEFT JOIN r_static_stream_vars b ON a.sid = b.stream_id WHERE a.status = 1 LIMIT ?, ?";

    const STREAM_FETCH_BY_ID    = "SELECT a.sid, a.uid, a.name, a.permalink, a.info, a.hashtags, a.cover, a.created, b.bookmarks_count, b.listeners_count
                                   FROM r_streams a LEFT JOIN r_static_stream_vars b ON a.sid = b.stream_id
                                   WHERE (a.sid = :id) OR (a.permalink = :id AND a.permalink != '')";

    const STREAM_FETCH_SIMILAR  = "SELECT a.sid, a.uid, a.name, a.permalink, a.info, a.hashtags, a.cover, a.created, b.bookmarks_count, b.listeners_count
                                   FROM r_streams a LEFT JOIN r_static_stream_vars b ON a.sid = b.stream_id
                                   WHERE a.sid != :id AND a.permalink != :id AND MATCH(a.hashtags) AGAINST(
                                   (SELECT hashtags FROM r_streams WHERE (sid = :id) OR (permalink = :id AND permalink != ''))) LIMIT :max";

    const STREAM_FETCH_SEARCH   = "SELECT a.sid, a.uid, a.name, a.permalink, a.info, a.hashtags, a.cover, a.created, b.bookmarks_count, b.listeners_count
                                   FROM r_streams a LEFT JOIN r_static_stream_vars b ON a.sid = b.stream_id
                                   WHERE MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)
                                   LIMIT ?, ?";

    const STREAM_FETCH_HASHTAGS = "SELECT a.sid, a.uid, a.name, a.permalink, a.info, a.hashtags, a.cover, a.created, b.bookmarks_count, b.listeners_count
                                   FROM r_streams a LEFT JOIN r_static_stream_vars b ON a.sid = b.stream_id
                                   WHERE MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)
                                   LIMIT ?, ?";

    const USERS_FETCH_BY_LIST   = "SELECT uid, name, permalink, avatar FROM r_users WHERE FIND_IN_SET(uid, ?)";
    const USERS_FETCH_BY_ID     = "SELECT uid, name, permalink, avatar FROM r_users WHERE uid = ?";

    const MAXIMUM_SIMILAR_COUNT = 10;

    private static function getStreamsPrefix() {
        $fluentPDO = Database::getFluentPDO();

        return $fluentPDO
            ->from("r_streams a")->leftJoin("r_static_stream_vars b ON a.sid = b.stream_id")
            ->select("a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags", "a.cover", "a.created", "b.bookmarks_count", "b.listeners_count");
    }

    private static function getUsersPrefix() {
        $fluentPDO = Database::getFluentPDO();

        return $fluentPDO
            ->from("r_users")
            ->select("uid", "name", "permalink", "avatar");
    }

    public static function getStreamList($from = 0, $limit = 50) {
        $db = Database::getInstance();

        $involved_users = [];

        //$prepared_query = $db->query_quote(self::STREAM_FETCH_LIST, array($from, $limit));
        $prepared_query = self::getStreamsPrefix()->where("status = 1")->limit($limit)->offset($from)
            ->getQuery();

        $streams = $db->query_universal($prepared_query, null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            self::processStreamRow($row);
            return $row;
        });

        //$prepared_query = $db->query_quote(self::USERS_FETCH_BY_LIST, array(implode(',', $involved_users)));
        $prepared_query = self::getUsersPrefix()->where("FIND_IN_SET(uid, ?)", implode(',', $involved_users))
            ->getQuery();

        $users = $db->query_universal($prepared_query, 'uid', function ($row) {
            self::processUserRow($row);
            return $row;
        });

        return ['streams' => $streams, 'users' => $users];
    }

    public static function getStreamListFiltered($filter = "*", $from = 0, $limit = 50) {
        $db = Database::getInstance();

        $involved_users = [];

        if (substr($filter, 0, 1) === '#') {
            $prepared_query = $db->query_quote(self::STREAM_FETCH_HASHTAGS,
                array('+' . substr($filter, 1), $from, $limit));
        } else {
            $prepared_query = $db->query_quote(self::STREAM_FETCH_SEARCH,
                array(misc::searchQueryFilter($filter), $from, $limit));
        }

        $streams = $db->query_universal($prepared_query, null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            self::processStreamRow($row);
            return $row;
        });

        $prepared_query = $db->query_quote(self::USERS_FETCH_BY_LIST, array(implode(',', $involved_users)));

        $users = $db->query_universal($prepared_query, 'uid', function ($row) {
            self::processUserRow($row);
            return $row;
        });

        return ['streams' => $streams, 'users' => $users];
    }

    public static function getOneStream($id) {
        $db = Database::getInstance();

        $prepared_query = $db->query_quote(self::STREAM_FETCH_BY_ID, array(':id' => $id));

        $stream = $db->query_single_row($prepared_query);

        if($stream !== null) {
            self::processStreamRow($stream);
            $stream['owner'] = $db->query_single_row(self::USERS_FETCH_BY_ID, array($stream['uid']));
        }

        return $stream;
    }

    public static function getSimilarTo($id) {
        $db = Database::getInstance();

        $involved_users = [];

        $prepared_query = $db->query_quote(self::STREAM_FETCH_SIMILAR,
            array(':id' => $id, ':max' => self::MAXIMUM_SIMILAR_COUNT));

        $streams = $db->query_universal($prepared_query, null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            self::processStreamRow($row);
            return $row;
        });

        $prepared_query = $db->query_quote(self::USERS_FETCH_BY_LIST, array(implode(',', $involved_users)));
        $users = $db->query_universal($prepared_query, 'uid', function ($row) {
            self::processUserRow($row);
            return $row;
        });

        return ['streams' => $streams, 'users' => $users];
    }

    private static function processStreamRow(&$row) {
        $row['sid'] = (int) $row['sid'];
        $row['uid'] = (int) $row['uid'];

        $row['listeners_count'] = (int) $row['listeners_count'];
        $row['bookmarks_count'] = (int) $row['bookmarks_count'];

        $row['cover_url'] = Folders::genStreamCoverUrl($row['cover']);
        $row['key'] = empty($row['permalink']) ? $row['sid'] : $row['permalink'];
        $row['hashtags_array'] = strlen($row['hashtags']) ? preg_split("/\\s*\\,\\s*/", $row['hashtags']) : null;
    }

    private static function processUserRow(&$row) {
        $row['uid'] = (int) $row['uid'];

        $row['avatar_url'] = Folders::genAvatarUrl($row['avatar']);
        $row['key'] = empty($row['permalink']) ? $row['uid'] : $row['permalink'];
    }

}