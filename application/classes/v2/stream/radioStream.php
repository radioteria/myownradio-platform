<?php

/**
 * Main class for manipulation with streams
 *
 * @author Roman
 */
class radioStream {

    protected $object, $database, $visitor;
    private $stream_id;
    private $write;

    const DB_STREAM_FETCH =
        "SELECT * FROM r_streams WHERE (:sid != '') AND (sid = :sid OR permalink = :sid)";
    const DB_STREAM_SET_STATE =
        "UPDATE r_streams SET status = ? WHERE sid = ?";
    const DB_STREAM_MODIFY =
        "UPDATE r_streams SET name = ?, info = ?, genres = ?, permalink = ?, category = ? WHERE sid = ?";
    const DB_STREAM_DELETE_ALL_TRACKS =
        "DELETE FROM r_link WHERE stream_id = ?";
    const DB_STREAM_DELETE =
        "DELETE FROM r_streams WHERE sid = ?";
    const DB_STREAM_REMOVE_COVER =
        "UPDATE r_streams SET cover = NULL WHERE sid = ?";
    const DB_STREAM_UPDATE_COVER =
        "UPDATE r_streams SET cover = ? WHERE sid = ?";

    public function __construct($stream_id, $write = false) {
        $this->database = Database::getInstance();
        $this->visitor = User::getInstance();
        $this->reload($stream_id);

        /* CHECK PERMISSION */
        if (($write === true) && ($this->visitor->getId() !== $this->getDetails()->getOwner())) {
            throw new streamException("You are not allowed to modify this stream", 1004);
        }

        $this->write = $write;
        $this->stream_id = (int)$this->object['sid'];
    }

    public function getDetails() {
        return new radioStreamInfo($this->object);
    }

    public function getTrackList() {
        return new radioStreamTrackList($this->object['sid'], $this);
    }

    public function getHelper() {
        return new radioStreamHelper($this->object);
    }

    public function getStreamingURL() {
        return sprintf("http://myownradio.biz:7778/audio?s=%d", $this->stream_id);
    }

    public function toArray() {
        return $this->object;
    }

    public function getId() {
        return (int)$this->object['sid'];
    }


    public function reload($stream_id = null) {
        if ($stream_id === null) {
            $stream_id = $this->getId();
        }

        $prepared_query = $this->database->query_quote(self::DB_STREAM_FETCH, array(':sid' => $stream_id));

        $result = $this->database->query_single_row($prepared_query);

        if ($result === null) {
            throw new streamException("Stream not exists", 1002);
        }

        $result['cover_url'] = Folders::genStreamCoverUrl($result['cover']);

        $this->object = $result;

        return $this;
    }

    public function setState(validStreamState $state) {
        $result = $this->database->query_update(self::DB_STREAM_SET_STATE, array($state, $this->stream_id));

        if ($result === 0) {
            throw new streamException("Stream not modified", 1001, null);
        }

        $this->reload();
        $this->notifyStreamers();

        return misc::okJSON(array(array("STREAM_MODIFY" => $this->reload()->toArray())));
    }

    public function notifyStreamers() {
        $ch = curl_init('127.0.0.1:7778/notify?s=' . $this->getId());
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        $result = curl_exec($ch);
        misc::writeDebug("Notify result -> " . $result);
        curl_close($ch);
    }

    public function modify(validStreamName $name, validStreamDescription $info, String $genres, validPermalink $permalink, validCategory $category) {
        $result = $this->database->query_update(self::DB_STREAM_MODIFY,
            array($name, $info, $genres, $permalink, $category, $this->getId()));

        if ($result === 0) {
            throw new streamException("Stream not modified", 1001, null);
        }

        return misc::okJSON(array(array("STREAM_MODIFY" => $this->reload()->toArray())));
    }

    public function delete() {
        $this->_purgeTracks();
        $this->_deleteStream();

        $this->notifyStreamers();

        return misc::okJSON(array(array('STREAM_DELETE' => array('id' => $this->object['sid']))));
    }

    public function purge() {
        $this->_purgeTracks();

        $this->notifyStreamers();

        return misc::okJSON(array(array('STREAM_PURGE' => array('id' => $this->object['sid']))));
    }

    // Remove all tracks from stream
    private function _purgeTracks() {
        $this->database->query_update(self::DB_STREAM_DELETE_ALL_TRACKS, array($this->object['sid']));
    }

    // Delete stream
    private function _deleteStream() {
        $this->database->query_update(self::DB_STREAM_DELETE, array($this->object['sid']));
    }

    public function deletePicture() {
        if ($pictureFile = $this->getDetails()->getCoverFile()) {
            $pfPrefix = ApplicationConfig::getSetting("content", "content_folder")
                . ApplicationConfig::getSetting("content", "stream_pictures")
                . "/";
            try {
                (new File($pfPrefix . $pictureFile))->delete();
            }
            catch (patFileNotFoundException $ex) {
                /* NOP */
            }
        }

        $result = $this->database->query_update(self::DB_STREAM_REMOVE_COVER, array($this->stream_id));

        if ($result === 1) {
            return misc::okJSON();
        } else {
            return misc::errJSON("Stream has no picture");
        }
    }

    public function changePicture($file) {

        $current_picture = $this->getDetails()->getCoverFile();

        if ($current_picture !== null && $current_picture->exists()) {
            $current_picture->delete();
        }

        $new_picture_filename = sprintf("stream%05d_%s_%s", $this->stream_id, $this->randomizeName(), strtolower($file['name']));
        $new_picture_path = Folders::genStreamCoverPath($new_picture_filename);

        move_uploaded_file($file['tmp_name'], $new_picture_path);

        $this->database->query_update(self::DB_STREAM_UPDATE_COVER, array($new_picture_filename, $this->stream_id));

        echo misc::okJSON(Folders::genStreamCoverUrl($new_picture_filename));
    }

    public function randomizeName($length = 8) {
        $chars = str_split("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890");
        $builder = "";
        for ($i = 0; $i < $length; $i++) {
            $builder .= $chars[(int)rand(0, count($chars) - 1)];
        }
        return $builder;
    }

}
