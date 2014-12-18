<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 18.12.14
 * Time: 9:01
 */

namespace Model\Beans;

/**
 * @table r_tracks
 * @key tid
 */
class TrackBean implements BeanObject {

    protected
        $tid, $uid, $filename, $ext,
        $artist, $title, $album,
        $track_number, $genre, $date,
        $duration, $filesize, $color,
        $uploaded;

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

    public function getFilename() {
        return $this->filename;
    }

    public function getFilesize() {
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

    public function getOwnerID() {
        return $this->uid;
    }

    public function getUploaded() {
        return $this->uploaded;
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

    public function setDuration($duration) {
        $this->duration = $duration;
    }

    public function setExt($ext) {
        $this->ext = $ext;
    }

    public function setFilename($filename) {
        $this->filename = $filename;
    }

    public function setFilesize($filesize) {
        $this->filesize = $filesize;
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

    public function setUid($uid) {
        $this->uid = $uid;
    }

    public function setUploaded($uploaded) {
        $this->uploaded = $uploaded;
    }

}