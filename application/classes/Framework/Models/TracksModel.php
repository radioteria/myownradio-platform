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
use Framework\Services\Config;
use Framework\Services\DB\DBQuery;
use Framework\Services\Injectable;
use Objects\PlaylistTrack;
use Objects\Track;
use Tools\Common;
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
     * @throws \Framework\Exceptions\ApplicationException
     * @throws \Framework\Exceptions\ControllerException
     */
    public function upload(array $file, Optional $addToStream, $upNext = false) {

        $config = Config::getInstance();

        if (array_search($file['type'], $config->getSetting('upload', 'supported_audio')->get()) === false) {
            throw new ControllerException("Unsupported type of file: " . $file["type"]);
        }

        $audioTags = Common::getAudioTags($file['tmp_name']);

        $maximalDuration = $config->getSetting('upload', 'maximal_length')->get();

        $duration = $audioTags['DURATION']
            ->getOrElseThrow(new ControllerException("Uploaded file has zero duration"));


        $uploadTimeLeft = $this->user->getActivePlan()->getUploadLimit() - $this->user->getTracksDuration() - $duration;

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
        $track->setTrackNumber($audioTags["TRACKNUMBER"]->getOrElseEmpty());
        $track->setArtist($audioTags["PERFORMER"]->getOrElseEmpty());
        $track->setTitle($audioTags["TITLE"]->getOrElse($file['name']));
        $track->setAlbum($audioTags["ALBUM"]->getOrElseEmpty());
        $track->setGenre($audioTags["GENRE"]->getOrElseEmpty());
        $track->setDate($audioTags["RECORDED_DATE"]->getOrElseEmpty());
        $track->setDuration($duration);
        $track->setFileSize(filesize($file["tmp_name"]));
        $track->setUploaded(time());
        $track->setColor(0);
        $track->setCopyOf(null);

        $track->save();

        $result = move_uploaded_file($file['tmp_name'], $track->getOriginalFile());

        if ($result !== false) {

            $this->addToStream($track, $addToStream, $upNext);

        } else {

            $track->delete();

            throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

        }

    }

    public function grabCurrentTrack($fromID, Optional $streamID, $upNext = false) {

        $current = PlaylistTrack::getCurrent($fromID);

        $current->then(function($track) use ($streamID, $upNext) {
            /** @var PlaylistTrack $track */
            self::grabTrack1(Track::getByID($track->getID())->get(), $streamID, $upNext);
        });

    }


    public function grabTrack($trackID, Optional $addToStream, $upNext = false) {

        /** @var Track $source */
        $source = Track::getByID($trackID)
            ->getOrElseThrow(ControllerException::noTrack($trackID));

        $this->grabTrack1($source, $addToStream, $upNext);

    }

    private function addToStream(Track $track, Optional $stream, $upNext = false) {

        $stream->then(function ($streamID) use ($track, $upNext) {

            $streamObject = new PlaylistModel($streamID);
            $streamObject->addTracks($track->getID(), $upNext);

        });

    }


    private function grabTrack1(Track $track, Optional $addToStream, $upNext = false) {

        if ($track->getUserID() == $this->user->getID()) {

            $this->addToStream($track, $addToStream, $upNext);

        }

        $uploadTimeLeft = $this->user->getActivePlan()->getUploadLimit() -
            $this->user->getTracksDuration();

        if ($uploadTimeLeft < $track->getDuration()) {
            throw new ControllerException("You are exceeded available upload time. Please upgrade your account.");
        }

        $destination = $track->cloneObject();

        $destination->setUserID($this->user->getID());
        $destination->setUploaded(time());
        $destination->setColor(0);
        $destination->setCopyOf($track->getID());

        $destination->save();

        $tempFile = $destination->getOriginalFile() . ".temp";

        if (file_exists($track->getOriginalFile()) && copy($track->getOriginalFile(), $tempFile)) {

            rename($tempFile, $destination->getOriginalFile());

            $this->addToStream($destination, $addToStream, $upNext);

        } else {

            $destination->delete();

        }

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