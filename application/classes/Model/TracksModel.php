<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:11
 */

namespace Model;


use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Exceptions\UnauthorizedException;
use MVC\Services\Config;
use MVC\Services\Database;
use MVC\Services\Injectable;
use Objects\Track;
use Tools\Common;
use Tools\Optional;
use Tools\Singleton;

class TracksModel {

    use Singleton, Injectable;

    /** @var UserModel $user */

    protected $user;

    function __constructor() {
        $this->user = AuthUserModel::getInstance();
    }

    /**
     * @param array $file
     * @param Optional $addToStream
     * @throws \MVC\Exceptions\ApplicationException
     * @throws \MVC\Exceptions\ControllerException
     */
    public function upload(array $file, Optional $addToStream) {

        $config = Config::getInstance();

        if(array_search($file['type'], $config->getSetting('upload', 'supported_audio')->get()) === false) {
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

        $track->save();

        $result = move_uploaded_file($file['tmp_name'], $track->getOriginalFile());

        if ($result !== false) {

            $addToStream->then(function ($streamID) use ($track) {

                $streamObject = new PlaylistModel($streamID);
                $streamObject->addTracks($track->getID());

            });


        } else {

            $track->delete();

            throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

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
            } catch (UnauthorizedException $e) { /* NOP */ }
        }

    }

    /**
     * @param $tracks
     */
    public function deleteFromStreams($tracks) {

        $streams = Database::doInConnection(function (Database $db) use ($tracks) {

            $query = $db->getDBQuery()
                ->selectFrom("r_link")
                ->select("stream_id")
                ->selectAlias("GROUP_CONCAT(unique_id)", "unique_ids")
                ->where("FIND_IN_SET(track_id, ?)", $tracks)
                ->addGroupBy("stream_id");

            return $db->fetchAll($query);

        });

        foreach($streams as $streamID => $uniqueIDs) {

            (new PlaylistModel($streamID))
                ->removeTracks($uniqueIDs);

        }

    }


} 