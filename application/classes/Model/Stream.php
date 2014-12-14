<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 16:06
 */

namespace Model;


use Model\Traits\StreamTracksList;
use MVC\Exceptions\ControllerException;
use ReflectionClass;
use Tools\Singleton;

class Stream extends Model {

    use Singleton, StreamTracksList;

    private $bean_key = "sid";
    private $bean_fields = [
        "sid", "uid", "name", "permalink", "info", "status", "started", "started_from",
        "access", "category", "hashtags", "cover", "created"
    ];
    private $bean_update = [
        "name", "permalink", "info", "access", "category", "hashtags"
    ];

    protected $sid;

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

    public function __construct($sid) {
        parent::__construct();
        $this->load($sid);
    }

    private function load($sid) {

        $user = User::getInstance()->getId();

        $object = $this->db->fetchOneRow("SELECT * FROM r_streams WHERE sid = ?", [$sid])
            ->getOrElseThrow(ControllerException::noStream($sid));

        if (intval($object["uid"]) !== $user) {
            throw ControllerException::noPermission();
        }

        try {
            $reflection = new ReflectionClass($this);
            foreach ($this->bean_fields as $field) {
                $prop = $reflection->getProperty($field);
                $prop->setAccessible(true);
                $prop->setValue($this, $object[$field]);
            }
        } catch (\ReflectionException $exception) {
            throw new ControllerException($exception->getMessage());
        }
    }

    public function save() {

        $fluent = $this->db->getFluentPDO();
        $query = $fluent->update("r_streams");

        try {

            $reflection = new ReflectionClass($this);

            $keyProperty = $reflection->getProperty($this->bean_key);
            $keyProperty->setAccessible(true);
            $query->where($this->bean_key, $keyProperty->getValue($this));

            foreach ($this->bean_update as $field) {
                $property = $reflection->getProperty($field);
                $property->setAccessible(true);
                $query->set($property->getName(), $property->getValue($this));
            }

            $this->db->executeUpdate($query->getQuery(), $query->getParameters());

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

    /**
     * @param mixed $access
     */
    public function setAccess($access) {
        $this->access = $access;
    }

    /**
     * @param mixed $category
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * @param mixed $hashtags
     */
    public function setHashtags($hashtags) {
        $this->hashtags = $hashtags;
    }

    /**
     * @param mixed $info
     */
    public function setInfo($info) {
        $this->info = $info;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @param mixed $permalink
     */
    public function setPermalink($permalink) {
        $this->permalink = $permalink;
    }



} 