<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:11
 */

namespace Framework\Models;


use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Services\Config;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\HttpRequest;
use Objects\Track;
use REST\Playlist;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class TracksModel implements Injectable, SingletonInterface {

    use Singleton;

    /** @var AuthUserModel $user */

    protected $user;

    function __construct() {
        $this->user = AuthUserModel::getInstance();
    }


    public function getUserModel() {
        return $this->user;
    }

    /**
     * @param array $file
     * @param Optional $addToStream
     * @param bool $upNext
     * @param bool $skipCopies
     * @throws \Framework\Exceptions\ApplicationException
     * @throws \Framework\Exceptions\ControllerException
     * @return Track
     */
    public function upload(array $file, Optional $addToStream, $upNext = false, $skipCopies = false) {

        $config = Config::getInstance();
        $request = HttpRequest::getInstance();

        $id3 = new \getID3();

        $request->getLanguage()->then(function ($language) use ($id3) {
            if (array_search($language, array('uk', 'ru')) !== false) {
                $id3->encoding_id3v1 = "cp1251";
            }
        });

        $meta = $id3->analyze($file["tmp_name"]);

        \getid3_lib::CopyTagsToComments($meta);

        $maximalDuration  = $config->getSetting('upload', 'maximal_length')->get();
        $availableFormats = $config->getSetting('upload', 'supported_audio')->get();

        if (array_search($file['type'], $availableFormats) === false) {
            throw new ControllerException("Unsupported type format");
        }

        if (empty($meta["audio"]["bitrate"])) {
            throw new ControllerException("File appears to be broken");
        }

        if ($skipCopies && isset($meta["comments"]["title"][0]) && isset($meta["comments"]["artist"][0])) {
            if ($copy = $this->getSameTrack($meta["comments"]["title"][0], $meta["comments"]["artist"][0])) {
                throw new ControllerException("Track with same artist and title already exists", $copy);
            }
        }

        $duration = $meta["comments"]["length"][0];

        $uploadTimeLeft = $this->user->getCurrentPlan()->getTimeMax() - $this->user->getTracksDuration() - $duration;

        if ($duration > $maximalDuration) {
            throw new ControllerException("Uploaded file is too long: " . $duration);
        }

        if ($uploadTimeLeft < $duration) {
            throw new ControllerException("You are exceeded available upload time. Please upgrade your account.");
        }

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $track = new Track();

        $track->setUserID($this->user->getID());
        $track->setFileName($file["name"]);
        $track->setExtension($extension);
        $track->setTrackNumber(
            isset($meta["comments"]["track_number"][0]) ? $meta["comments"]["track_number"][0] : ""
        );
        $track->setArtist(
            isset($meta["comments"]["artist"][0]) ? $meta["comments"]["artist"][0] : ""
        );
        $track->setTitle(
            isset($meta["comments"]["title"][0]) ? $meta["comments"]["title"][0] : $file['name']
        );
        $track->setAlbum(
            isset($meta["comments"]["album"][0]) ? $meta["comments"]["album"][0] : ""
        );
        $track->setGenre(
            isset($meta["comments"]["genre"][0]) ? $meta["comments"]["genre"][0] : ""
        );
        $track->setDate(
            isset($meta["comments"]["date"][0]) ? $meta["comments"]["date"][0] : ""
        );
        $track->setDuration($duration);
        $track->setFileSize(filesize($file["tmp_name"]));
        $track->setUploaded(time());
        $track->setColor(0);
        $track->setCopyOf(null);

        $track->save();

        error_log("SRC: " . $file['tmp_name']);
        error_log("DST: " . $track->getOriginalFile());

        $result = move_uploaded_file($file['tmp_name'], $track->getOriginalFile());

        if ($result !== false) {

            $this->addToStream($track, $addToStream, $upNext);

            logger(sprintf("User #%d uploaded new track: %s (upload time left: %d seconds)",
                $track->getUserID(), $track->getFileName(), $uploadTimeLeft / 1000));

            return Playlist::getInstance()->getOneTrack($track->getID());

        } else {

            $track->delete();

            throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

        }


    }

    /**
     * @param $title
     * @param $artist
     * @return bool
     */
    public function getSameTrack($title, $artist) {

        if (!$title || !$artist) {
            return null;
        }

        return (new SelectQuery("r_tracks"))
            ->where("title LIKE ?", [$title])
            ->where("artist LIKE ?", [$artist])
            ->where("uid", $this->user->getID())
            ->limit(1)
            ->fetchOneRow()
            ->getOrElseNull();

    }

    private function addToStream(Track $track, Optional $stream, $upNext = false) {

        if (!$stream->validate()) {
            return Optional::noValue();
        }

        $streamID = $stream->get();

        $streamObject = new PlaylistModel($streamID);

        $streamObject->addTracks($track->getID(), $upNext);

    }

    /**
     * @param $tracks
     */
    public function delete($tracks) {

        foreach (explode(",", $tracks) as $track) {
            try {
                $track = new TrackModel($track);
                $track->delete();
            } catch (UnauthorizedException $e) { /* NOP */
            }
        }

    }

    /**
     * @param $tracks
     */
    public function deleteFromStreams($tracks) {

        $db = DBQuery::getInstance();

        $streams = $db->selectFrom("r_link")
            ->select("stream_id")
            ->selectAlias("GROUP_CONCAT(unique_id)", "unique_ids")
            ->where("track_id", explode(",", $tracks))
            ->addGroupBy("stream_id")->fetchAll();

        foreach ($streams as $stream) {

            $model = new PlaylistModel($stream['stream_id']);
            $model->removeTracks($stream['unique_ids']);
            unset($model);

        }

    }



} 