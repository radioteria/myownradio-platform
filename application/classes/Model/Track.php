<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 21:55
 */

namespace Model;


use Model\Traits\Bean;
use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\Config;
use ReflectionClass;
use Tools\Singleton;

class Track extends Model {

    use Singleton;

    protected $bean_type = "track";
    protected $bean_key = "tid";
    protected $bean_fields = [
        "tid", "uid", "filename", "ext", "artist", "title", "album", "track_number",
        "genre", "date", "duration", "filesize", "color", "uploaded"
    ];
    protected $bean_update = [
        "artist", "title", "album", "track_number", "genre", "date", "color"
    ];
    protected  $key;

    protected $tid;
    protected $uid;
    protected $filename;
    protected $ext;
    protected $artist;
    protected $title;
    protected $album;
    protected $track_number;
    protected $genre;
    protected $date;
    protected $duration;
    protected $filesize;
    protected $color;
    protected $uploaded;

    public function __construct($id) {
        parent::__construct();
        $this->key = $id;
        $this->reload();
    }

    public function reload() {

        $userID = AuthorizedUser::getInstance()->getId();

        $object = $this->db->fetchOneRow("SELECT * FROM r_tracks WHERE tid = ?", [$this->key])
            ->getOrElseThrow(ControllerException::noTrack($this->key));

        if (intval($object["uid"]) !== $userID) {
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

        return $this;

    }

    public function save() {

        $fluent = $this->db->getFluentPDO();
        $query = $fluent->update("r_tracks");

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
     * @return string
     */
    public function getAlbum() {
        return $this->album;
    }

    /**
     * @return string
     */
    public function getArtist() {
        return $this->artist;
    }

    /**
     * @return int
     */
    public function getColor() {
        return intval($this->color);
    }

    /**
     * @return int
     */
    public function getDate() {
        return intval($this->date);
    }

    /**
     * @return int
     */
    public function getDuration() {
        return intval($this->duration);
    }

    /**
     * @return string
     */
    public function getExtension() {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getFileSize() {
        return intval($this->filesize);
    }

    /**
     * @return string
     */
    public function getGenre() {
        return $this->genre;
    }

    /**
     * @return int
     */
    public function getId() {
        return intval($this->tid);
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->title;
    }

    /**
     * @return string
     */
    public function getTrackNumber() {
        return $this->track_number;
    }

    /**
     * @return int
     */
    public function getUid() {
        return intval($this->uid);
    }

    /**
     * @return int
     */
    public function getUploaded() {
        return intval($this->uploaded);
    }

    public function getOriginalFile() {
        $config = Config::getInstance();
        return sprintf("%s/ui_%d/a_%03d_original.%s",
            $config->getSetting("content", "content_folder")->getOrElseThrow(
                ApplicationException::of("CONTENT FOLDER NOT SET")),
            $this->getUid(),
            $this->getId(),
            $this->getExtension()
        );
    }

}