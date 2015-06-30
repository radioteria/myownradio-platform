<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 21:55
 */

namespace Framework\Models;


use Framework\Exceptions\Auth\NoPermissionException;
use Framework\Exceptions\ControllerException;
use Framework\FileServer\FileServerFacade;
use Framework\FileServer\FSFile;
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class TrackModel
 * @package Framework\Models
 * @localized 21.05.2015
 */
class TrackModel extends Model implements SingletonInterface {

    use Singleton;

    protected $key;

    /** @var UserModel $user */
    protected $user;

    /** @var Track $object */
    protected $object;

    /**
     * @param int|Track $id
     */
    public function __construct($id) {

        parent::__construct();

        $this->user = AuthUserModel::getInstance();

        if ($id instanceof Track) {
            $this->key = $id->getID();
            $this->object = $id;
        } else {
            $this->key = $id;
            $this->reload();
        }

        $this->checkAccess();

    }

    /**
     * @throws \Framework\Exceptions\ControllerException
     * @return $this
     */
    public function reload() {

        $this->object = Track::getByID($this->key)
            ->getOrThrow(ControllerException::noTrack($this->key));

    }

    public function checkAccess() {
        if ($this->object->getUserID() != $this->user->getID()) {
            throw new NoPermissionException();
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
     * @return string
     */
    public function getFileUrl() {
        /** @var FileServerFile $file */
        $file = FileServerFile::getByID($this->object->getFileId())
            ->getOrThrow(I18n::tr("ERROR_TRACK_NOT_AVAILABLE", [$this->object->getID()]));

        return FileServerFacade::getServerNameById($file->getServerId()) . $file->getFileHash();
    }

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

    /**
     * @return int
     */
    public function getUserID() {
        return $this->object->getUserID();
    }

    /**
     * @return string
     */
    public function getFileName() {
        return $this->object->getFileName();
    }

}