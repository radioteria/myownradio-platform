<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 05.04.15
 * Time: 13:28
 */

namespace API\REST;


use Framework\Context;
use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Models\AuthUserModel;
use Framework\Services\Database;
use Framework\Services\DB\Query\SelectQuery;
use Tools\Common;
use Tools\Singleton;
use Tools\SingletonInterface;
use Tools\System;

/**
 * Class ChannelsCollection
 * @package API
 */
class ChannelsCollection implements Injectable, SingletonInterface {

    use Singleton;

    const CHANNELS_PER_REQUEST_MAX = 50;
    const CHANNELS_SUGGESTION_MAX = 5;
    const CHANNELS_SIMILAR_MAX = 10;
    const CHANNEL_PUBLIC = "PUBLIC";

    /** @var Context $context */
    private $context;

    function __construct() {
        $this->context = Context::getInstance();
    }


    /**
     * @return SelectQuery
     */
    private function channelPrefix() {

        $owner = AuthUserModel::getAuthorizedUserID();

        $prefix = (new SelectQuery("r_streams a"))
            ->innerJoin("r_static_stream_vars b", "a.sid = b.stream_id")
            ->select(["a.sid", "a.uid", "a.name", "a.permalink", "a.info", "a.hashtags", "a.access", "a.status",
                "a.cover", "a.cover_background", "a.created", "b.bookmarks_count", "b.listeners_count", "b.is_featured",
                "b.playbacks"]);

        $prefix->where("(a.status = 1 AND b.tracks_count > 0)");

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

    private function addNowPlaying(SelectQuery $query) {

        $query->innerJoin("r_link d", "d.stream_id = a.sid");
        $query->innerJoin("r_tracks e", "e.tid = d.track_id");

        $query->where("(d.time_offset < MOD(:micro - (a.started - a.started_from), b.tracks_duration))", [
            ":micro" => System::time() - Defaults::SCHEDULE_TIME_SHIFT
        ]);
        $query->where("(d.time_offset + e.duration > MOD(:micro - (a.started - a.started_from), b.tracks_duration))");

        $query->select("CONCAT(e.artist, IF(e.artist != '', ' - ', ''), e.title) as now_playing");
        $query->select("e.duration - (MOD(:micro - (a.started - a.started_from), b.tracks_duration) - d.time_offset) as time_left");

        $query->addGroupBy("a.sid");

    }

    public function getUpcomingChange(array $channels = null, $threshold = 5000) {
        $query = $this->channelPrefix();
        $query->where("a.sid", $channels);
        $this->addNowPlaying($query);
        $query->limit(1);
        $query->orderBy("time_left ASC");
        return $query->fetchAll();
    }

    /**
     * @param $channel_id
     * @return mixed
     */
    public function getOneChannel($channel_id) {

        $query = $this->channelPrefix();

        $query->where("((a.sid = :key) OR (a.permalink IS NOT NULL AND a.permalink = :key))", [":key" => $channel_id]);

        return $query->fetchOneRow()->getOrElseThrow(ControllerException::noStream($channel_id));

    }

    public function getRandomChannel() {

        $query = $this->channelPrefix();
        $sum = (new SelectQuery("r_static_stream_vars"))->select("sum(summary_played)")->fetchOneColumn()->get();
        $rand = rand(0, $sum);
        $ch = (new SelectQuery("r_static_stream_vars, (SELECT @acc := 0) t"))->select("stream_id", "@acc:=@acc+summary_played AS acc")
            ->having("acc >= ?", [$rand])->fetchOneColumn()->get();
        $query->where("sid", $ch);

        return $query->fetchOneRow()->getOrElseThrow(ControllerException::of("No available channels found!"));

    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsPopular($offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->where("b.playbacks > 0 OR b.listeners_count > 0");

        $query->orderBy("b.summary_played DESC, b.playbacks DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];

    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsList($offset = 0, $limit = null) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        if (is_numeric($offset)) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
    }

    public function getNewChannelsList($offset = 0, $limit = null) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        if (is_numeric($offset)) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        //$query->where("a.created")

        $query->orderBy("a.created DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];

    }

    /**
     * @param int $category_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByCategory($category_id, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $query->where("a.category", $category_id);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("b.bookmarks_count DESC, b.summary_played DESC, b.playbacks DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
    }

    /**
     * @param string $filter
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListBySearch($filter, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $escaped = Common::searchQueryFilter($filter);

        $query->select("MATCH(a.name, a.permalink, a.hashtags) AGAINST (:req IN BOOLEAN MODE) as search");
        $query->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (:req IN BOOLEAN MODE)", [":req" => $escaped]);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("search DESC, b.summary_played DESC, b.listeners_count DESC, b.playbacks DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
    }

    /**
     * @param string $tag
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByTag($tag, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $query->select("MATCH(a.hashtags) AGAINST (:tag IN BOOLEAN MODE) as tag");
        $query->where("MATCH(a.hashtags) AGAINST (:tag IN BOOLEAN MODE)", [":tag" => "+".$tag]);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("tag DESC, b.summary_played DESC, b.listeners_count DESC, b.playbacks DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
    }

    /**
     * @param int $user_id
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getChannelsListByUser($user_id, $offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $query->where("a.uid", $user_id);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $query->orderBy("a.created DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
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
        $escaped = Common::searchQueryFilter($filter);

        $query->select("MATCH(a.name, a.permalink, a.hashtags) AGAINST (:search IN BOOLEAN MODE) AS search");
        $query->where("MATCH(a.name, a.permalink, a.hashtags) AGAINST (:search IN BOOLEAN MODE)", [":search" => $escaped]);

        $query->limit(self::CHANNELS_SUGGESTION_MAX);

        $query->orderBy("search DESC, b.summary_played DESC, b.listeners_count DESC, b.playbacks DESC");

        return $query->fetchAll();

    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getBookmarkedChannels($offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        if (is_numeric($offset) && $offset >= 0) {
            $query->offset($offset);
        }

        if (is_numeric($limit)) {
            $query->limit(min($limit, self::CHANNELS_PER_REQUEST_MAX));
        }

        $user_id = AuthUserModel::getAuthorizedUserID();


        $query->innerJoin("r_bookmarks f", "f.stream_id = a.sid");
        $query->where("f.user_id", $user_id);

        $query->orderBy("f.date DESC");

        return [
            "count" => count($query),
            "items" => $query->fetchAll()
        ];
    }

    /**
     * @param $channel_id
     * @return array
     */
    public function getSimilarChannels($channel_id) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $query->where("a.sid != :id");
        $query->where("a.permalink != :id");
        $query->where("MATCH(a.hashtags) AGAINST((SELECT hashtags FROM r_streams WHERE (sid = :id) OR (permalink = :id AND permalink IS NOT NULL)))", [
            ':id' => $channel_id
        ]);

        $query->limit(self::CHANNELS_SIMILAR_MAX);

        return [
            "items" => $query->fetchAll()
        ];
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return array
     */
    public function getRecentChannels($offset = 0, $limit = self::CHANNELS_PER_REQUEST_MAX) {

        $query = $this->channelPrefix();

        $this->addNowPlaying($query);

        $query->offset($offset);
        $query->limit($limit);

        $query->orderBy("a.started DESC");

        return [
            "items" => $query->fetchAll()
        ];
    }
} 
