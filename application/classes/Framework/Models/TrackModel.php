<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 21:55
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\FileServer\FileServerFacade;
use Framework\FileServer\FSFile;
use Framework\Services\Config;
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class TrackModel extends Model implements SingletonInterface {

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
     * @throws \Framework\Exceptions\ControllerException
     * @return $this
     */
    public function reload() {

        $this->object = Track::getByID($this->key)
            ->getOrElseThrow(ControllerException::noTrack($this->key));

        if (! $this->object->isAccessibleTo($this->user->getUserObject())) {
            throw UnauthorizedException::noPermission();
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
    public function getFileUrl() {
        /** @var FileServerFile $file */
        $file = FileServerFile::getByID($this->object->getFileId())
            ->getOrElseThrow(I18n::tr("CEX_TRACK_FILE_NULL", ["id" => $this->object->getID()]));

        return FileServerFacade::getServerNameById($file->getServerId()).$file->getFileHash();
    }

/*    public function edit($artist, $title, $album, $trackNR, $genre, $date, $color) {

        $artist ->then(function ($artist)   { $this->object->setArtist($artist); });
        $title  ->then(function ($title)    { $this->object->setTitle($title); });
        $album  ->then(function ($album)    { $this->object->setAlbum($album); });
        $trackNR->then(function ($trackNR)  { $this->object->setTrackNumber($trackNR); });
        $genre  ->then(function ($genre)    { $this->object->setGenre($genre); });
        $date   ->then(function ($date)     { $this->object->setDate($date); });
        $color  ->then(function ($color)    { $this->object->setColor($color); });

        $this->object->save();

        return $this;

    }*/

    public function changeColor($color) {

        $this->object->setColor($color);
        $this->object->save();

    }

    /**
     * @return void
     */
    public function delete() {

        error_log(sprintf("User #%d is deleting track %s", $this->getUserID(), $this->getFileName()));
        FSFile::deleteLink($this->object->getFileId());
        $this->object->delete();

    }

}