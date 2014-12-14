<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 16:06
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use ReflectionClass;
use Tools\Singleton;

class Stream extends Model {

    use Singleton;

    private $key;

    private $sid;
    private $uid;
    private $name;
    private $permalink;
    private $info;

    private $status;
    private $started;
    private $started_from;
    private $access;
    private $category;
    private $hashtags;
    private $cover;
    private $created;

    public function __construct($streamId) {
        parent::__construct();
        $this->key = $streamId;
        $this->load();
    }

    private function load() {
        $object = $this->db->fetchOneRow("SELECT * FROM r_streams WHERE sid = ? OR (permalink = ? AND permalink != '')",
            [$this->key])->getOrElseThrow(ControllerException::noStream($this->key));
        try {
            $reflection = new ReflectionClass($this);
            foreach ($object as $key=>$value) {
                $prop = $reflection->getProperty($key);
                $prop->setAccessible(true);
                $prop->setValue($this, $value);
            }
        } catch (\ReflectionException $exception) {
            throw new ControllerException($exception->getMessage());
        }
    }

    /**
     * @return mixed
     */
    public function getSid() {
        return $this->sid;
    }

    /**
     * @return mixed
     */
    public function getStarted() {
        return $this->started;
    }

    /**
     * @return mixed
     */
    public function getAccess() {
        return $this->access;
    }

    /**
     * @return mixed
     */
    public function getCategory() {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getCover() {
        return $this->cover;
    }

    /**
     * @return mixed
     */
    public function getCreated() {
        return $this->created;
    }

    /**
     * @return \MVC\Services\Database
     */
    public function getDb() {
        return $this->db;
    }

    /**
     * @return mixed
     */
    public function getHashtags() {
        return $this->hashtags;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @return mixed
     */
    public function getKey() {
        return $this->key;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getPermalink() {
        return $this->permalink;
    }

    /**
     * @return mixed
     */
    public function getStartedFrom() {
        return $this->started_from;
    }

    /**
     * @return mixed
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * @return mixed
     */
    public function getUid() {
        return $this->uid;
    }



} 