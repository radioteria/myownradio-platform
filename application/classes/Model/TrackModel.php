<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 21:55
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use MVC\Exceptions\UnauthorizedException;
use MVC\Services\Config;
use Objects\Track;
use Tools\Singleton;

class TrackModel extends Model {

    use Singleton;

    protected $key;

    /** @var UserModel $user */
    protected $user;

    /** @var Track $object */
    protected $object;

    public function __construct($id) {

        parent::__construct();

        $this->user = AuthUserModel::getInstance();
        $this->key = $id;
        $this->reload();

    }

    /**
     * @return $this
     * @throws \MVC\Exceptions\UnauthorizedException
     */
    public function reload() {

        $this->object = Track::getByID($this->key)
            ->getOrElseThrow(ControllerException::noTrack($this->key));

        if ($this->object->getUserID() != $this->user->getID()) {
            throw UnauthorizedException::noAccess();
        }

    }

    public function save() {

        $this->object->save();

    }

    /**
     * @return string
     */
    public function getAlbum() {
        return $this->object->getAlbum();
    }

    /**
     * @return string
     */
    public function getArtist() {
        return $this->object->getArtist();
    }

    /**
     * @return int
     */
    public function getColor() {
        return $this->object->getColor();
    }

    /**
     * @return int
     */
    public function getDate() {
        return $this->object->getDate();
    }

    /**
     * @return int
     */
    public function getDuration() {
        return $this->object->getDuration();
    }

    /**
     * @return string
     */
    public function getExtension() {
        return $this->object->getExtension();
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->object->getFileName();
    }

    /**
     * @return int
     */
    public function getFileSize() {
        return $this->object->getFileSize();
    }

    /**
     * @return string
     */
    public function getGenre() {
        return $this->object->getGenre();
    }

    /**
     * @return int
     */
    public function getID() {
        return $this->getID();
    }

    /**
     * @return string
     */
    public function getTitle() {
        return $this->object->getTitle();
    }

    /**
     * @return string
     */
    public function getTrackNumber() {
        return $this->object->getTrackNumber();
    }

    /**
     * @return int
     */
    public function getUserID() {
        return $this->object->getUserID();
    }

    /**
     * @return int
     */
    public function getUploaded() {
        return $this->object->getUploaded();
    }

    /**
     * @return string
     */
    public function getOriginalFile() {
        return $this->object->getOriginalFile();
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

        $this->object->setArtist($artist);
        $this->object->setTitle($title);
        $this->object->setAlbum($album);
        $this->object->setTrackNumber($trackNR);
        $this->object->setGenre($genre);
        $this->object->setDate($date);
        $this->object->setColor($color);

        $this->object->save();

    }

    /**
     * @return void
     */
    public function delete() {

        unlink($this->getOriginalFile());

        $this->object->delete();

    }

    public function preview() {

        $config = Config::getInstance();

        $trackFile = $this->getOriginalFile();
        $streamer = $config->getSetting("streaming", "track_preview")->get();

        $command = sprintf($streamer, escapeshellarg($trackFile));

        $fh = popen($command, "r");

        header("Content-Type: audio/mpeg");

        while($data = fread($fh, 4096)) {
            echo $data;
            flush();
        }

        pclose($fh);

    }

}