<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 21:50
 */

namespace REST;


use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
use MVC\Services\DB\DBQuery;
use MVC\Services\DB\Query\SelectQuery;
use MVC\Services\Injectable;
use Tools\Common;
use Tools\Singleton;

class Streams {

    use Singleton, Injectable;

    const MAXIMUM_SIMILAR_COUNT = 10;

    /**
     * @return SelectQuery
     */
    private function getStreamsPrefix() {

        $prefix = (new SelectQuery("r_streams a"))
            ->leftJoin("r_static_stream_vars b", "a.sid = b.stream_id")
            ->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags",
                "a.cover", "a.created", "b.bookmarks_count", "b.listeners_count"]);

        return $prefix;

    }

    /**
     * @return SelectQuery
     */
    private function getUsersPrefix() {

        $prefix = (new SelectQuery("r_users"))
            ->select(["uid", "name", "permalink", "avatar"]);

        return $prefix;

    }

    /**
     * @param $id
     * @return array
     */
    public function getOneStream($id) {

        $stream = Database::doInTransaction(function(Database $db) use ($id) {

            $queryStream = $this->getStreamsPrefix();
            $queryStream->where("(a.sid = :id) OR (a.permalink = :id)", [':id' => $id]);

            $stream = $db->fetchOneRow($queryStream)
                ->getOrElseThrow(new ControllerException("Stream not found"));

            $this->processStreamRow($stream);

            $queryUser = $this->getUsersPrefix()->where('uid', $stream['uid']);

            $stream['owner'] = $db->fetchOneRow($queryUser)
                ->getOrElseThrow(new ControllerException("Stream owner not found"));

            return $stream;

        });

        return $stream;

    }

    /**
     * @param string $filter
     * @param int $category
     * @param int $from
     * @param int $limit
     * @return array
     */
    public function getStreamListFiltered($filter = null, $category = null, $from = 0, $limit = 50) {

        $result = Database::doInTransaction(function (Database $db, DBQuery $query)
                                            use ($filter, $category, $from, $limit) {

            $involved_users = [];

            $queryStream = $this->getStreamsPrefix();

            if (is_numeric($category)) {
                $queryStream->where("a.category", $category);
            }

            if (empty($filter)) {

                /* No Operation */

            } else if (substr($filter, 0, 1) === '#') {
                $queryStream->where("MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                    '+' . substr($filter, 1));
            } else {
                $queryStream->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                    Common::searchQueryFilter($filter));
            }

            $queryStream->where("a.status = 1");
            $queryStream->limit($limit)->offset($from);

            $streams = $db->fetchAll($queryStream, null, null, function ($row) use (&$involved_users) {
                if (array_search($row['uid'], $involved_users) === false) {
                    $involved_users[] = $row['uid'];
                }
                $this->processStreamRow($row);
                return $row;
            });

            $users = $this->getUsersList($db, $involved_users);

            return ['streams' => $streams, 'users' => $users];

        });

        return $result;

    }

    /**
     * @param $id
     * @return array
     */
    public function getSimilarTo($id) {

        $result = Database::doInTransaction(function (Database $db, DBQuery $query) use ($id) {

            $involved_users = [];

            $queryStream = $this->getStreamsPrefix();
            $queryStream->where("a.sid != :id");
            $queryStream->where("a.permalink != :id");
            $queryStream->where("a.status", 1);
            $queryStream->where("MATCH(a.hashtags) AGAINST((SELECT hashtags FROM r_streams WHERE (sid = :id) OR (permalink = :id)))",
                [':id' => $id]);
            $queryStream->limit(self::MAXIMUM_SIMILAR_COUNT);

            $streams = $db->fetchAll($queryStream, null, null, function ($row) use (&$involved_users) {
                if (array_search($row['uid'], $involved_users) === false) {
                    $involved_users[] = $row['uid'];
                }
                $this->processStreamRow($row);
                return $row;
            });

            $users = $this->getUsersList($db, $involved_users);

            return ['streams' => $streams, 'users' => $users];

        });

        return $result;

    }



    /**
     * @param $row
     */
    private function processStreamRow(&$row) {
        $row['sid'] = (int) $row['sid'];
        $row['uid'] = (int) $row['uid'];

        $row['listeners_count'] = (int) $row['listeners_count'];
        $row['bookmarks_count'] = (int) $row['bookmarks_count'];

        $row['cover_url'] = \Folders::genStreamCoverUrl($row['cover']);
        $row['key'] = empty($row['permalink']) ? $row['sid'] : $row['permalink'];
        $row['hashtags_array'] = strlen($row['hashtags']) ? preg_split("/\\s*\\,\\s*/", $row['hashtags']) : null;
    }

    /**
     * @param $row
     */
    private function processUserRow(&$row) {
        $row['uid'] = (int) $row['uid'];

        $row['avatar_url'] = \Folders::genAvatarUrl($row['avatar']);

        $row['key'] = empty($row['permalink']) ? $row['uid'] : $row['permalink'];
    }

    /**
     * @param Database $db
     * @param array $users
     * @return SelectQuery
     */
    private function getUsersList(Database $db, array $users) {
        $fluent = $this->getUsersPrefix();
        $fluent->where("uid", $users);
        $users = $db->fetchAll($fluent, null, "uid", function ($row) {
            $this->processUserRow($row);
            return $row;
        });
        return $users;
    }

} 