<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 16:06
 */

namespace Model;


use Model\Traits\StreamTracksList;
use MVC\Exceptions\ControllerException;
use MVC\Services\Database;
use ReflectionClass;
use Tools\Singleton;

class Stream extends Model {

    use Singleton;

    private $bean_key = "sid";
    private $bean_fields = [
        "sid", "uid", "name", "permalink", "info", "status", "started", "started_from",
        "access", "category", "hashtags", "cover", "created"
    ];
    private $bean_update = [
        "name", "permalink", "info", "access", "category", "hashtags"
    ];

    protected $key;

    protected $sid;
    protected $uid;
    protected $name;
    protected $permalink;
    protected $info;

    protected $status;
    protected $started;
    protected $started_from;
    protected $access;
    protected $category;
    protected $hashtags;
    protected $cover;
    protected $created;

    /** @var UserModel $user */
    protected $user;

    public function __construct($id) {
        parent::__construct();
        $this->user = AuthUserModel::getInstance();
        $this->key = $id;
        $this->load();
    }

    private function load() {

        $object = Database::doInConnection(function (Database $db) {

            return $db->fetchOneRow("SELECT * FROM r_streams WHERE sid = ?", [$this->key])
                ->getOrElseThrow(ControllerException::noStream($this->key));

        });

        if (intval($object["uid"]) !== $this->user->getId()) {
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

        Database::doInConnection(function (Database $db) {

            $query = $db->getDBQuery()->updateTable("r_streams");

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

                $db->executeUpdate($query);

                $db->commit();

            } catch (\ReflectionException $exception) {
                throw new ControllerException($exception->getMessage());
            }

        });

    }


    /**
     * @return int
     */
    public function getId() {
        return intval($this->sid);
    }

    /**
     * @return int
     */
    public function getStarted() {
        return intval($this->started);
    }

    /**
     * @return string
     */
    public function getAccess() {
        return $this->access;
    }

    /**
     * @return int|null
     */
    public function getCategory() {
        return is_null($this->category) ? null : intval($this->category);
    }

    /**
     * @return string
     */
    public function getCover() {
        return $this->cover;
    }

    /**
     * @return int
     */
    public function getCreated() {
        return intval($this->created);
    }

    /**
     * @return string
     */
    public function getHashTags() {
        return $this->hashtags;
    }

    /**
     * @return string
     */
    public function getInfo() {
        return $this->info;
    }

    /**
     * @return string
     */
    public function getName() {
        return $this->name;
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
    public function getStartedFrom() {
        return intval($this->started_from);
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
    public function getUid() {
        return intval($this->uid);
    }


    /**
     * @param string $access
     */
    public function setAccess($access) {
        $this->access = $access;
    }

    /**
     * @param int|null $category
     */
    public function setCategory($category) {
        $this->category = $category;
    }

    /**
     * @param string $hashtags
     */
    public function setHashTags($hashtags) {
        $this->hashtags = $hashtags;
    }

    /**
     * @param string $info
     */
    public function setInfo($info) {
        $this->info = $info;
    }

    /**
     * @param string $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @param string|null $permalink
     */
    public function setPermalink($permalink) {
        $this->permalink = $permalink;
    }

} 