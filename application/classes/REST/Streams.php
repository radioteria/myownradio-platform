<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 21:50
 */

namespace REST;


use MVC\Exceptions\ControllerException;
use MVC\Services\Injectable;
use SelectQuery;
use Tools\Common;
use Tools\Database;
use Tools\Singleton;

class Streams {

    use Singleton, Injectable;

    /** @var Database */
    private $db;

    const MAXIMUM_SIMILAR_COUNT = 10;

    function __construct() {
        $this->db = Database::getInstance();
    }


    /**
     * @return SelectQuery
     */
    private function getStreamsPrefix() {
        $fluent = $this->db->getFluentPDO();
        return $fluent
            ->from("r_streams a")->leftJoin("r_static_stream_vars b ON a.sid = b.stream_id")
            ->select(null)->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags",
                "a.cover", "a.created", "b.bookmarks_count", "b.listeners_count"])
            ->where("a.status = 1");
    }

    /**
     * @return SelectQuery
     */
    private function getUsersPrefix() {

        $fluentPDO = $this->db->getFluentPDO();
        return $fluentPDO->from("r_users")->select(null)->select(["uid", "name", "permalink", "avatar"]);

    }

    /**
     * @param $id
     * @return array
     */
    public function getOneStream($id) {

        $fluent = $this->getStreamsPrefix();
        $fluent->where("(a.sid = :id) OR (a.permalink = :id AND a.permalink != '')", [':id' => $id]);

        $stream = $this->db->fetchOneRow($fluent->getQuery(false), $fluent->getParameters())
            ->getOrElseThrow(new ControllerException("Stream not found"));

        $this->processStreamRow($stream);

        $fluent = $this->getUsersPrefix()->where('uid', $stream['uid']);

        $stream['owner'] = $this->db->fetchOneRow($fluent->getQuery(false), $fluent->getParameters())
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

        $fluent = $this->getStreamsPrefix();

        if (is_numeric($category)) {
            $fluent->where("a.category", $category);
        }

        if (empty($filter)) {

            /* No Operation */

        } else if (substr($filter, 0, 1) === '#') {
            $fluent->where("MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                '+' . substr($filter, 1));
        } else {
            $fluent->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)",
                Common::searchQueryFilter($filter));
        }

        $fluent->limit($limit)->offset($from);

        $prepared_query = $this->db->query_quote($fluent->getQuery(false), $fluent->getParameters());

        $streams = $this->db->fetchAll($prepared_query, null, null, function ($row) use (&$involved_users) {
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
     * @param array $users
     * @return SelectQuery
     */
    private function getUsersList(array $users) {
        $fluent = self::getUsersPrefix();
        $fluent->where("uid", $users);
        $users = $this->db->fetchAll($fluent->getQuery(false), $fluent->getParameters(), "uid", function ($row) {
            self::processUserRow($row);
            return $row;
        });
        return $users;
    }

} 