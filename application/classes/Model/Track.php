<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 21:55
 */

namespace Model;


use Model\Traits\Bean;
use Tools\Singleton;

class Track extends Model {

    use Singleton, Bean;

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

    public function __construct($trackId) {
        parent::__construct();
        $this->key = $trackId;
        $this->reload();
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
    public function getExt() {
        return $this->ext;
    }

    /**
     * @return string
     */
    public function getFilename() {
        return $this->filename;
    }

    /**
     * @return int
     */
    public function getFilesize() {
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
    public function getTid() {
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



}