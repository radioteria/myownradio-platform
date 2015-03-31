<?php
/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 18.12.14
 * Time: 9:01
 */

namespace Objects;

use Framework\Exceptions\ControllerException;
use Framework\FileServer\FileServerFacade;
use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;
use Objects\FileServer\FileServerFile;
use Tools\Folders;

/**
 * @table r_tracks
 * @key tid
 */
class Track extends ActiveRecordObject implements ActiveRecord {

    protected $tid, $file_id, $uid, $filename, $hash, $ext,
        $artist, $title, $album,
        $track_number, $genre, $date, $cue, $buy,
        $duration, $filesize, $color = 0,
        $uploaded, $copy_of = null, $used_count = 0,
        $is_new = 1, $can_be_shared;

    /*
     * Bean Getters
     */
    public function getAlbum() {
        return $this->album;
    }

    public function getArtist() {
        return $this->artist;
    }

    public function getColor() {
        return $this->color;
    }

    public function getDate() {
        return $this->date;
    }

    public function getDuration() {
        return $this->duration;
    }

    public function getExtension() {
        return $this->ext;
    }

    public function getFileName() {
        return $this->filename;
    }

    public function getFileSize() {
        return $this->filesize;
    }

    public function getGenre() {
        return $this->genre;
    }

    public function getID() {
        return $this->tid;
    }

    public function getTitle() {
        return $this->title;
    }

    public function getTrackNumber() {
        return $this->track_number;
    }

    public function getUserID() {
        return $this->uid;
    }

    public function getUploaded() {
        return $this->uploaded;
    }

    public function getCopyOf() {
        return $this->copy_of;
    }

    public function getUsedCount() {
        return $this->used_count;
    }

    /**
     * @return mixed
     */
    public function getCue() {
        return $this->cue;
    }

    /**
     * @return int
     */
    public function getIsNew() {
        return $this->is_new;
    }



    /*
     * Bean Setters
     */

    public function setAlbum($album) {
        $this->album = $album;
    }

    public function setArtist($artist) {
        $this->artist = $artist;
    }

    public function setColor($color) {
        $this->color = $color;
    }

    public function setDate($date) {
        $this->date = $date;
    }

    public function setGenre($genre) {
        $this->genre = $genre;
    }

    public function setTitle($title) {
        $this->title = $title;
    }

    public function setTrackNumber($track_number) {
        $this->track_number = $track_number;
    }

    public function setDuration($duration) {
        $this->duration = $duration;
    }

    public function setExtension($ext) {
        $this->ext = $ext;
    }

    public function setFileName($filename) {
        $this->filename = $filename;
    }

    public function setFileSize($filesize) {
        $this->filesize = $filesize;
    }

    public function setUserID($uid) {
        $this->uid = $uid;
    }

    public function setUploaded($uploaded) {
        $this->uploaded = $uploaded;
    }

    public function getOriginalFile() {
        return Folders::getInstance()->getRealTrackPath($this);
    }

    public function setCopyOf($copy_of) {
        $this->copy_of = $copy_of;
    }

    public function setUsedCount($used_count) {
        $this->used_count = $used_count;
    }

    /**
     * @param mixed $cue
     */
    public function setCue($cue) {
        $this->cue = $cue;
    }

    /**
     * @param int $is_new
     */
    public function setIsNew($is_new) {
        $this->is_new = $is_new;
    }

    /**
     * @return mixed
     */
    public function getBuy() {
        return $this->buy;
    }

    /**
     * @param mixed $buy
     */
    public function setBuy($buy) {
        $this->buy = $buy;
    }

    /**
     * @param mixed $hash
     */
    public function setHash($hash) {
        $this->hash = $hash;
    }

    /**
     * @return mixed
     */
    public function getHash() {
        return $this->hash;
    }

    /**
     * @param mixed $file_id
     */
    public function setFileId($file_id) {
        $this->file_id = $file_id;
    }

    /**
     * @return mixed
     */
    public function getFileId() {
        return $this->file_id;
    }

    public function getFileUrl() {
        /** @var FileServerFile $file */
        $file = FileServerFile::getByID($this->getFileId())
            ->getOrElseThrow(new ControllerException(
                sprintf("Track \"%d\" is not uploaded to any file server", $this->getID())
            ));

        return FileServerFacade::getServerNameById($file->getServerId()).$file->getFileHash();
    }

    /**
     * @param mixed $can_be_shared
     */
    public function setCanBeShared($can_be_shared) {
        $this->can_be_shared = $can_be_shared;
    }

    /**
     * @return mixed
     */
    public function isCanBeShared() {
        return $this->can_be_shared;
    }

}