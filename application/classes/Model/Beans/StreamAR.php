<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 12:09
 */

namespace Model\Beans;

/**
 * Class StreamAR
 * @package Model\Beans
 * @table r_streams
 * @key sid
 *
 * @do_SEARCH_BY_HASHTAGS MATCH(hashtags) AGAINST(? IN BOOLEAN MODE)
 * @do_SEARCH_BY_ANYTHING MATCH(name, permalink, hashtags) AGAINST (? IN BOOLEAN MODE)
 */
class StreamAR implements ActiveRecord {

    use ARTools;

    protected $sid, $uid, $name, $permalink,
        $info, $status, $started, $started_from,
        $access, $category, $hashtags, $cover, $created;

    /**
     * @param $access
     * @return $this
     */
    public function setAccess($access) {
        $this->access = $access;
        return $this;
    }

    /**
     * @return string
     */
    public function getAccess() {
        return $this->access;
    }

    /**
     * @param mixed $category
     * @return $this;
     */
    public function setCategory($category) {
        $this->category = $category;
        return $this;
    }

    /**
     * @return int
     */
    public function getCategory() {
        return intval($this->category);
    }

    /**
     * @param string $cover
     * @return $this
     */
    public function setCover($cover) {
        $this->cover = $cover;
        return $this;
    }

    /**
     * @return string
     */
    public function getCover() {
        return $this->cover;
    }

    /**
     * @param int $created
     * @return $this
     */
    public function setCreated($created) {
        $this->created = $created;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreated() {
        return intval($this->created);
    }

    /**
     * @param string $hashtags
     * @return $this
     */
    public function setHashTags($hashtags) {
        $this->hashtags = $hashtags;
        return $this;
    }

    /**
     * @return string
     */
    public function getHashTags() {
        return $this->hashtags;
    }

    /**
     * @param string $info
     * @return $this
     */
    public function setInfo($info) {
        $this->info = $info;
        return $this;
    }

    /**
     * @return string
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @param string $name
     * @return $this
     */
    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param string|null $permalink
     * @return $this
     */
    public function setPermalink($permalink) {
        $this->permalink = $permalink;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPermalink() {
        return $this->permalink;
    }

    /**
     * @return int
     */
    public function getId() {
        return intval($this->sid);
    }

    /**
     * @param int $started
     * @return $this
     */
    public function setStarted($started) {
        $this->started = $started;
        return $this;
    }

    /**
     * @return int
     */
    public function getStarted() {
        return intval($this->started);
    }

    /**
     * @param int $started_from
     * @return $this
     */
    public function setStartedFrom($started_from) {
        $this->started_from = $started_from;
        return $this;
    }

    /**
     * @return int
     */
    public function getStartedFrom() {
        return intval($this->started_from);
    }

    /**
     * @param int $status
     * @return $this
     */
    public function setStatus($status) {
        $this->status = $status;
        return $this;
    }

    /**
     * @return int
     */
    public function getStatus() {
        return intval($this->status);
    }

    /**
     * @return int
     */
    public function getUserId() {
        return intval($this->uid);
    }

} 