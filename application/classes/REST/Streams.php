<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 21:50
 */

namespace REST;


use Framework\Exceptions\ControllerException;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\Injectable;
use Tools\Common;
use Tools\Folders;
use Tools\Singleton;

class Streams implements \Countable {

    use Singleton, Injectable;

    const MAXIMUM_SIMILAR_COUNT = 10;

    /**
     * @return SelectQuery
     */
    private function getStreamsPrefix() {

        $prefix = (new SelectQuery("r_streams a"))
            ->innerJoin("r_static_stream_vars b", "a.sid = b.stream_id")
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

        $queryStream = $this->getStreamsPrefix();
        $queryStream->where("(a.sid = :id) OR (a.permalink = :id)", [':id' => $id]);

        $stream = $queryStream->fetchOneRow()
            ->getOrElseThrow(new ControllerException("Stream not found"));

        $this->processStreamRow($stream);

        $queryUser = $this->getUsersPrefix()->where('uid', $stream['uid']);

        $stream['owner'] = $queryUser->fetchOneRow()
            ->getOrElseThrow(new ControllerException("Stream owner not found"));

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

            $streams = $queryStream->fetchAll(null, function ($row) use (&$involved_users) {
                if (array_search($row['uid'], $involved_users) === false) {
                    $involved_users[] = $row['uid'];
                }
                $this->processStreamRow($row);
                return $row;
            });

            $users = $this->getUsersList($involved_users);

            return ['streams' => $streams, 'users' => $users];

    }

    /**
     * @param $id
     * @return array
     */
    public function getSimilarTo($id) {

        $involved_users = [];

        $queryStream = $this->getStreamsPrefix();
        $queryStream->where("a.sid != :id");
        $queryStream->where("a.permalink != :id");
        $queryStream->where("a.status", 1);
        $queryStream->where("MATCH(a.hashtags) AGAINST((SELECT hashtags FROM r_streams WHERE (sid = :id) OR (permalink = :id)))",
            [':id' => $id]);
        $queryStream->limit(self::MAXIMUM_SIMILAR_COUNT);

        $streams = $queryStream->fetchAll(null, function ($row) use (&$involved_users) {
            if (array_search($row['uid'], $involved_users) === false) {
                $involved_users[] = $row['uid'];
            }
            $this->processStreamRow($row);
            return $row;
        });

        $users = $this->getUsersList($involved_users);

        return ['streams' => $streams, 'users' => $users];

    }

    /**
     * @param $row
     */
    private function processStreamRow(&$row) {

        $row['cover_url'] = Folders::getInstance()->genStreamCoverUrl($row['cover']);
        $row['key'] = empty($row['permalink']) ? $row['sid'] : $row['permalink'];
        $row['hashtags_array'] = strlen($row['hashtags']) ? preg_split("/\\s*\\,\\s*/", $row['hashtags']) : null;

    }

    /**
     * @param $row
     */
    private function processUserRow(&$row) {

        $row['avatar_url'] = Folders::getInstance()->genAvatarUrl($row['avatar']);
        $row['key'] = empty($row['permalink']) ? $row['uid'] : $row['permalink'];

    }

    /**
     * @param array $users
     * @return SelectQuery
     */
    private function getUsersList(array $users) {

        $query = $this->getUsersPrefix();
        $query->where("uid", $users);

        $users = $query->fetchAll("uid", function ($row) {
            $this->processUserRow($row);
            return $row;
        });

        return $users;

    }

    /**
     * @return int|mixed
     */
    public function count() {

        return count((new SelectQuery("r_streams"))->where("status", 1));

    }

}