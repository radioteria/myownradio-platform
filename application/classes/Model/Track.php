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
use MVC\Services\Database;
use ReflectionClass;
use Tools\Folders;
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

    /** @var User $user */
    protected $user;

    public function __construct($id) {
        parent::__construct();
        $this->user = AuthorizedUser::getInstance();
        $this->key = $id;
        $this->reload();
    }

    /**
     * @return $this
     * @throws \MVC\Exceptions\ControllerException
     */
    public function reload() {

        Database::doInConnection(function (Database $db) {

            $object = $db->fetchOneRow("SELECT * FROM r_tracks WHERE tid = ?", [$this->key])
                ->getOrElseThrow(ControllerException::noTrack($this->key));

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

        });



        return $this;

    }

    public function save() {

        Database::doInConnection(function (Database $db) {

            $query = $db->getDBQuery()->updateTable("r_tracks");

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

    /**
     * @return string
     */
    public function getOriginalFile() {
        return Folders::getInstance()->getRealTrackPath($this);
    }

    /**
     * @param $artist
     * @param $title
     * @param $album
     * @param $trackNR
     * @param $genre
     * @param $date
     * @param $color
     */
    public function edit($artist, $title, $album, $trackNR, $genre, $date, $color) {

        $this->artist       = $artist;
        $this->title        = $title;
        $this->album        = $album;
        $this->track_number = $trackNR;
        $this->genre        = $genre;
        $this->date         = $date;
        $this->color        = $color;

        $this->save();

    }

    /**
     * @return void
     */
    public function delete() {

        unlink($this->getOriginalFile());

        Database::doInConnection(function (Database $db) {
            $db->executeUpdate("DELETE FROM r_tracks WHERE tid = ?", [$this->key]);
        });


    }

}