<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:11
 */

namespace Framework\Models;


use Framework\Defaults;
use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Framework\FileServer\Exceptions\NoSpaceForUploadException;
use Framework\FileServer\FSFile;
use Framework\Injector\Injectable;
use Framework\Services\Config;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\DB\Query\SelectQuery;
use Framework\Services\HttpRequest;
use Framework\Services\Locale\I18n;
use Framework\Services\Locale\L10n;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use REST\Playlist;
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
     * @param bool $skipCopies
     * @throws \Framework\Exceptions\ApplicationException
     * @throws \Framework\Exceptions\ControllerException
     * @return Track
     */
    public function upload(array $file, Optional $addToStream, $upNext = false, $skipCopies = false) {

        $config = Config::getInstance();
        $request = HttpRequest::getInstance();

        $id3 = new \getID3();
        $currentPlan = $this->user->getCurrentPlan();

        $request->getLanguage()->then(function ($language) use ($id3) {
            if (array_search($language, array('uk', 'ru')) !== false) {
                $id3->encoding_id3v1 = "cp1251";
            }
        });

        $meta = $id3->analyze($file["tmp_name"]);
        $hash = hash_file(Defaults::HASHING_ALGORITHM, $file["tmp_name"]);
        $duration = Common::getAudioDuration($file["tmp_name"])->getOrElseThrow(
            new ControllerException(L10n::tr("UPLOAD_FILE_BROKEN", [$file["name"]]))
        );

        \getid3_lib::CopyTagsToComments($meta);

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
        $basename = pathinfo($file["name"], PATHINFO_FILENAME);

        $maximalDuration = $config->getSetting('upload', 'maximal_length')->get();
        $availableFormats = $config->getSetting('upload', 'supported_extensions')->get();

        if (!preg_match("~^({$availableFormats})$~i", $extension)) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_UNSUPPORTED", [$extension]));
        }

        if ($copy = $this->getSameTrack($hash)) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_EXISTS", [$file["name"]]));
        }

        $uploadTimeLeft = $currentPlan->getTimeMax() - $this->user->getTracksDuration() - $duration;

        if ($duration > $maximalDuration) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_LONG", [$file["name"]]));
        }

        if ($duration < $currentPlan->getMinTrackLength()) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_SHORT", [$file["name"]]));
        }

        if ($uploadTimeLeft < $duration) {
            throw new ControllerException(I18n::tr("UPLOAD_NO_SPACE"));
        }

        $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

        $track = new Track();

        $track->setUserID($this->user->getID());
        $track->setFileName($file["name"]);
        $track->setHash($hash);
        $track->setExtension($extension);
        $track->setTrackNumber(
            isset($meta["comments"]["track_number"][0]) ? $meta["comments"]["track_number"][0] : ""
        );
        $track->setArtist(
            isset($meta["comments"]["artist"][0]) ? $meta["comments"]["artist"][0] : ""
        );
        $track->setTitle(
            isset($meta["comments"]["title"][0]) ? $meta["comments"]["title"][0] : $basename
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
        $track->setFileSize($file["size"]);
        $track->setUploaded(time());
        $track->setColor(0);
        $track->setCopyOf(null);

        try {
            $file_id = FSFile::registerLink($file["tmp_name"], $hash);
            $track->setFileId($file_id);
            $track->save();
        } catch (LocalFileNotFoundException $exception) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_BROKEN", [$file["name"]]));
        } catch (NoSpaceForUploadException $exception) {
            throw new ControllerException(I18n::tr("UPLOAD_NO_SERVERS", [$file["name"]]));
        }

        $this->addToStream($track, $addToStream, $upNext);

        error_log(sprintf("User #%d uploaded new track: %s (upload time left: %d seconds)",
            $track->getUserID(), $track->getFileName(), $uploadTimeLeft / 1000));

        return Playlist::getInstance()->getOneTrack($track->getID());


    }

    /**
     * @param $trackId
     * @param null|\Tools\Optional $destinationStream
     * @param bool $upNext
     * @return mixed
     * @throws \Framework\Exceptions\ControllerException
     */
    public function copy($trackId, Optional $destinationStream = null, $upNext = false) {
        /** @var Track $trackObject */
        $trackObject = Track::getByID($trackId)->getOrElseThrow(ControllerException::noTrack($trackId));

        if ($trackObject->getUserID() == $this->user->getID()) {
            throw new ControllerException(I18n::tr("COPY_FILE_YOURS", [$trackObject->getFileName()]));
        }

        if ($copy = $this->getSameTrack($trackObject->getHash())) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_EXISTS", [$trackObject->getFileName()]));
        }

        if (!$trackObject->isCanBeShared()) {
            throw new ControllerException(I18n::tr("COPY_FILE_PROTECTED", [$trackObject->getFileName()]));
        }

        $currentPlan = $this->user->getCurrentPlan();

        $uploadTimeLeft = $currentPlan->getTimeMax() - $this->user->getTracksDuration() - $trackObject->getDuration();

        if ($uploadTimeLeft < $trackObject->getDuration()) {
            throw new ControllerException(I18n::tr("UPLOAD_NO_SPACE"));
        }

        $copy = $trackObject->cloneObject();

        $copy->setUserID($this->user->getID());
        $copy->setCopyOf($trackObject->getID());
        $copy->setUsedCount(0);
        $copy->setUploaded(time());
        $copy->setColor(0);
        $copy->save();

        $db = Database::getInstance();
        $db->connect()->beginTransaction();
        FileServerFile::getByID($copy->getFileId())->then(function (FileServerFile $file) use ($db) {
            $file->setUseCount($file->getUseCount() + 1);
            $file->save();
            $db->commit();
        });
        Database::killInstance();

        $this->addToStream($copy, $destinationStream, $upNext);

        error_log(sprintf("User #%d cloned track: %s (upload time left: %d seconds)",
            $copy->getUserID(), $copy->getFileName(), $uploadTimeLeft / 1000));

        return Playlist::getInstance()->getOneTrack($copy->getID());

    }

    /**
     * @param $hash
     * @return bool
     */
    public function getSameTrack($hash) {

        /** @var Track $copy */
        $copy = Track::getByFilter("hash = ? AND uid = ?", [$hash, $this->user->getID()])->getOrElseNull();

        if (is_null($copy)) {
            return $copy;
        } else {
            return Playlist::getInstance()->getOneTrack($copy->getID());
        }

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