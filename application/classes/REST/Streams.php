<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 21:50
 */

namespace REST;


use Framework\Defaults;
use Framework\Exceptions\Auth\NoUserByLoginException;
use Framework\Exceptions\ControllerException;
use Framework\Injector\Injectable;
use Framework\Models\AuthUserModel;
use Framework\Models\UserModel;
use Framework\Services\DB\Query\SelectQuery;
use Objects\User;
use Tools\Common;
use Tools\Folders;
use Tools\Singleton;
use Tools\SingletonInterface;

class Streams implements \Countable, Injectable, SingletonInterface {

    use Singleton;

    const MAXIMUM_SIMILAR_COUNT = 10;

    /**
     * @return SelectQuery
     */
    private function getStreamsPrefix() {

        $prefix = (new SelectQuery("r_streams a"))
            ->innerJoin("r_static_stream_vars b", "a.sid = b.stream_id")
            ->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags", "a.category", "a.status",
                "a.access", "a.cover", "a.cover_background", "a.created", "b.bookmarks_count", "b.listeners_count",
                "b.tracks_count", "b.tracks_duration"]);

        return $prefix;

    }

    /**
     * @return SelectQuery
     */
    private function getUsersPrefix() {

        $prefix = (new SelectQuery("r_users"))
            ->select(["uid", "login", "name", "permalink", "avatar"]);

        return $prefix;

    }

    /**
     * @param $id
     * @return array
     */
    public function getOneStream($id) {

        $userId = AuthUserModel::getAuthorizedUserID();

        $queryStream = $this->getStreamsPrefix();
        $queryStream->where("(a.sid = :id) OR (a.permalink = :id)", [':id' => $id]);

        if (is_numeric($userId)) {
            $queryStream->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = ${userId}");
            $queryStream->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $queryStream->select("0 as bookmarked");
        }

        $stream = $queryStream->fetchOneRow()
            ->getOrThrow(new ControllerException("Stream not found"));

        $this->processStreamRow($stream);

        $queryUser = $this->getUsersPrefix()->where('uid', $stream['uid']);

        $stream["owner"] = $queryUser->fetchOneRow()
            ->getOrThrow(new ControllerException("Stream owner not found"));

        $this->processUserRow($stream["owner"]);

        return $stream;

    }

    /**
     * @param string $filter
     * @param int|null $category
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getStreamListFiltered($filter = null, $category = null, $offset = 0,
                                          $limit = Defaults::DEFAULT_STREAMS_PER_REQUEST) {

        $involved_users = [];

        $queryStream = $this->getStreamsPrefix();

        if (!is_null($category)) {
            $queryStream->where("a.category", $category);
        }

        /** @var UserModel $owner */
        $owner = AuthUserModel::getAuthorizedUserID();

        if (empty($filter)) {

            /* No Operation */

        } else if (substr($filter, 0, 1) === '#') {

            $queryStream->where("MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)", ['+' . substr($filter, 1)]);

        } else {

            $queryStream->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)", [
                Common::searchQueryFilter($filter)]);

        }

        $queryStream->where("a.status = 1");

        if ($owner !== null) {
            $queryStream->where("(a.access = ? OR a.uid = ?)", ["PUBLIC", $owner]);
        } else {
            $queryStream->where("a.access", "PUBLIC");
        }

        $queryStream->limit($limit)->offset($offset);

        $queryStream->orderBy("created DESC");

        $userId = AuthUserModel::getAuthorizedUserID();

        if (is_numeric($userId)) {
            $queryStream->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = ${userId}");
            $queryStream->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $queryStream->select("0 as bookmarked");
        }

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

        /** @var UserModel $owner */
        $owner = AuthUserModel::getAuthorizedUserID();

        $involved_users = [];

        $queryStream = $this->getStreamsPrefix();
        $queryStream->where("a.sid != :id");
        $queryStream->where("a.permalink != :id");
        $queryStream->where("a.status", 1);

        if ($owner !== null) {
            $queryStream->where("(a.access = ? OR a.uid = ?)", ["PUBLIC", $owner]);
        } else {
            $queryStream->where("a.access", "PUBLIC");
        }

        $queryStream->where("MATCH(a.hashtags) AGAINST((SELECT hashtags FROM r_streams WHERE (sid = :id) OR (permalink = :id)))",
            [':id' => $id]);
        $queryStream->limit(self::MAXIMUM_SIMILAR_COUNT);

        $userId = AuthUserModel::getAuthorizedUserID();

        if (is_numeric($userId)) {
            $queryStream->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = ${userId}");
            $queryStream->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $queryStream->select("0 as bookmarked");
        }

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
     * @param UserModel $user
     * @param int $offset
     * @return array
     */
    public function getBookmarksByUser(UserModel $user, $offset = 0) {

        $query = $this->getStreamsPrefix();

        $query->offset($offset);
        $query->limit(Defaults::DEFAULT_STREAMS_PER_REQUEST);

        $userId = AuthUserModel::getAuthorizedUserID();

        if (is_numeric($userId)) {
            $query->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = ${userId}");
            $query->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $query->select("0 as bookmarked");
        }

        $query->where("a.sid IN (SELECT stream_id FROM r_bookmarks WHERE user_id = ?)", [$user->getID()]);

        $streams = $query->fetchAll(null, function ($row) {
            $this->processStreamRow($row);
            return $row;
        });

        return $streams;

    }

    public function getByUser($userKey) {

        /** @var User $user */
        $user = User::getByFilter("FIND_BY_KEY", [ ":key" => $userKey ])
            ->getOrThrow(NoUserByLoginException::class, $userKey);

        $query = $this->getStreamsPrefix();
        $query->where("a.uid", [ $user->getID() ]);

        $userId = AuthUserModel::getAuthorizedUserID();

        if (is_numeric($userId)) {
            $query->where("(a.access = ? OR a.uid = ?)", ["PUBLIC", $userId]);
            $query->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = ${userId}");
            $query->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $query->where("a.access", "PUBLIC");
            $query->select("0 as bookmarked");
        }

        $streams = $query->fetchAll(null, function ($row) {
            $this->processStreamRow($row);
            return $row;
        });

        return $streams;

    }


    /**
     * @param $row
     */
    private function processStreamRow(&$row) {

        $row['name'] = (string) $row['name'];
        $row['cover_url'] = Folders::getInstance()->genStreamCoverUrl($row['cover']);
        $row['key'] = empty($row['permalink']) ? $row['sid'] : $row['permalink'];
        $row['hashtags_array'] = strlen($row['hashtags']) ? preg_split("/\\s*\\,\\s*/", $row['hashtags']) : null;
        $row['url'] = Folders::getInstance()->genStreamPageUrl($row);
        $row['bookmarked'] = boolval($row['bookmarked']);

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

        if (count($users) == 0) return [];

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