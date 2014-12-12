<?php

/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 02.12.14
 * Time: 16:21
 */
class Streams extends Model {

    const MAXIMUM_SIMILAR_COUNT = 10;

    /**
     * @return SelectQuery
     */
    private static function getStreamsPrefix() {
        $fluent = Database::getFluentPDO();
        return $fluent
            ->from("r_streams a")->leftJoin("r_static_stream_vars b ON a.sid = b.stream_id")
            ->select(null)->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags",
                "a.cover", "a.created", "b.bookmarks_count", "b.listeners_count"])
            ->where("a.status = 1");
    }

    /**
     * @return SelectQuery
     */
    private static function getUsersPrefix() {

        $fluentPDO = Database::getFluentPDO();
        return $fluentPDO->from("r_users")->select(null)->select(["uid", "name", "permalink", "avatar"]);

    }

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
     * @param string $filter
     * @param int $category
     * @param int $from
     * @param int $limit
     * @return array
     */
    public static function getStreamListFiltered($filter = null, $category = null, $from = 0, $limit = 50) {

        $involved_users = [];

        $db = Database::getInstance();

        $fluent = self::getStreamsPrefix();

        if (is_numeric($category)) {
            $fluent->where("a.category", $category);
        }

        if (empty($filter)) {
            /* NOP */
        } else if (substr($filter, 0, 1) === '#') {
            $fluent->where("MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                '+' . substr($filter, 1));
        } else {
            $fluent->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                misc::searchQueryFilter($filter));
        }

        $fluent->limit($limit)->offset($from);

        $prepared_query = $db->query_quote($fluent->getQuery(false), $fluent->getParameters());

        $streams = $db->fetchAll($prepared_query, null, null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            self::processStreamRow($row);
            return $row;
        });

        $users = self::getUsersList($db, $involved_users);

        return ['streams' => $streams, 'users' => $users];

    }

    /**
     * @param $id
     * @return array
     */
    public static function getOneStream($id) {

        $db = Database::getInstance();

        $fluent = self::getStreamsPrefix();
        $fluent->where("(a.sid = :id) OR (a.permalink = :id AND a.permalink != '')", [':id' => $id]);

        $stream = $db->fetchOneRow($fluent->getQuery(false), $fluent->getParameters())
            ->getOrElseThrow(new streamException("Stream not found"));

        print_r(gettype($stream));

        if($stream !== null) {
            self::processStreamRow($stream);
            $fluent = self::getUsersPrefix()->where('uid', $stream['uid']);
            $stream['owner'] = $db->fetchOneRow($fluent->getQuery(false), $fluent->getParameters());
        }

        return $stream;

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

    /**
     * @param $row
     */
    private static function processStreamRow(&$row) {
        $row['sid'] = (int) $row['sid'];
        $row['uid'] = (int) $row['uid'];

        $row['listeners_count'] = (int) $row['listeners_count'];
        $row['bookmarks_count'] = (int) $row['bookmarks_count'];

        $row['cover_url'] = Folders::genStreamCoverUrl($row['cover']);
        $row['key'] = empty($row['permalink']) ? $row['sid'] : $row['permalink'];
        $row['hashtags_array'] = strlen($row['hashtags']) ? preg_split("/\\s*\\,\\s*/", $row['hashtags']) : null;
    }

    /**
     * @param $row
     */
    private static function processUserRow(&$row) {
        $row['uid'] = (int) $row['uid'];

        $row['avatar_url'] = Folders::genAvatarUrl($row['avatar']);
        $row['key'] = empty($row['permalink']) ? $row['uid'] : $row['permalink'];
    }

    /**
     * @param Database $db
     * @param array $users
     * @return SelectQuery
     */
    private static function getUsersList(Database $db, array $users) {
        $fluent = self::getUsersPrefix();
        $fluent->where("uid", $users);
        $users = $db->fetchAll($fluent->getQuery(false), $fluent->getParameters(), "uid", function ($row) {
            self::processUserRow($row);
            return $row;
        });
        return $users;
    }

}