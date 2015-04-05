<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 13:28
 */

namespace API;


use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Models\AuthUserModel;
use Framework\Services\DB\Query\SelectQuery;
use Tools\Common;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class ChannelsCollection
 * @package API
 */
class ChannelsCollection implements Injectable, SingletonInterface {

    use Singleton;

    const CHANNELS_PER_REQUEST_MAX = 100;
    const CHANNELS_SUGGESTION_MAX = 10;
    const CHANNEL_PUBLIC = "PUBLIC";

    /**
     * @return SelectQuery
     */
    private function channelPrefix() {

        $owner = AuthUserModel::getAuthorizedUserID();

        $prefix = (new SelectQuery("r_streams a"))
            ->innerJoin("r_static_stream_vars b", "a.sid = b.stream_id")
            ->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags", "a.access", "a.status",
                "a.cover", "a.cover_background", "a.created", "b.bookmarks_count", "b.listeners_count"]);

        $prefix->where("a.status = 1");

        if (is_numeric($owner)) {
            $prefix->where("(a.access = ? OR a.uid = ?)", [self::CHANNEL_PUBLIC, $owner]);
            $prefix->leftJoin("r_bookmarks c", "c.stream_id = a.sid AND c.user_id = {$owner}");
            $prefix->select("IF(c.user_id IS NOT NULL, 1, 0) as bookmarked");
        } else {
            $prefix->where("a.access", self::CHANNEL_PUBLIC);
            $prefix->select("0 as bookmarked");
        }

        return $prefix;

    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsList($offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return $query->fetchAll();

    }

    /**
     * @param int $category_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByCategory($category_id, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $query->where("a.category", $category_id);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return $query->fetchAll();

    }

    /**
     * @param string $filter
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListBySearch($filter, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $query->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)", [
            Common::searchQueryFilter($filter)
        ]);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return $query->fetchAll();

    }

    /**
     * @param string $tag
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByTag($tag, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $query->where("MATCH(a.hashtags) AGAINST (? IN BOOLEAN MODE)", ['+' . substr($tag, 1)]);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return $query->fetchAll();

    }

    /**
     * @param int $user_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByUser($user_id, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $query->where("a.uid", $user_id);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return $query->fetchAll();

    }

    /**
     * @param int $offset
     * @param int $limit
     * @throws UnauthorizedException
     * @return array
     */
    public function getChannelsListBySelf($offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $user = AuthUserModel::getInstance();
        return $this->getChannelsListByUser($user->getID(), $offset, $limit);

    }

    /**
     * @param string $filter
     * @return array
     */
    public function getChannelsSuggestion($filter) {

        $query = $this->channelPrefix();

        $query->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (? IN BOOLEAN MODE)", [
            Common::searchQueryFilter($filter)
        ]);

        $query->limit(self::CHANNELS_SUGGESTION_MAX);

        return $query->fetchAll();

    }

} 