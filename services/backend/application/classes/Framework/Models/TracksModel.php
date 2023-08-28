<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 20:11
 */

namespace Framework\Models;


use app\Services\FFmpegService;
use Framework\Defaults;
use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Framework\FileServer\Exceptions\NoSpaceForUploadException;
use Framework\FileServer\FSFile;
use Framework\Injector\Injectable;
use Framework\Services\Database;
use Framework\Services\DB\DBQuery;
use Framework\Services\HttpRequest;
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServerFile;
use Objects\Track;
use REST\Playlist;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class TracksModel implements Injectable, SingletonInterface
{

    use Singleton;

    /** @var AuthUserModel $user */

    protected $user;

    function __construct()
    {
        $this->user = AuthUserModel::getInstance();
    }


    public function getUserModel()
    {
        return $this->user;
    }

    /**
     * @param array $file
     * @param Optional $addToStream
     * @param bool $upNext
     * @param bool $skipCopies
     * @return Track
     * @throws ControllerException
     * @throws ApplicationException
     */
    public function upload(array $file, Optional $addToStream, $upNext = false, $skipCopies = false)
    {
        $ffmpegService = FFmpegService::getInstance();

        $extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
        $basename = pathinfo($file["name"], PATHINFO_FILENAME);

        $tempName = $file['tmp_name'];
        $tempNameWithExtension = "$tempName.$extension";

        // Temporarily rename temp file so ffprobe will easier recognize audio format
        rename($tempName, $tempNameWithExtension);
        $metadata = $ffmpegService->getCommonMetadata($tempNameWithExtension);
        rename($tempNameWithExtension, $tempName);

        if ($metadata === null) {
            throw new ControllerException("Unable to read audio file metadata.");
        }

        $request = HttpRequest::getInstance();

        $currentPlan = $this->user->getCurrentPlan();

        $request->getLanguage()->then(function ($language) {
            // @todo cp1251dec
            if (array_search($language, array('uk', 'ru')) !== false) {
                // Nothing
            }
        });

        $hash = hash_file(Defaults::HASHING_ALGORITHM, $file["tmp_name"]);
        $duration = $metadata["duration"];

        $maximalDuration = config('storage.audio.track_duration_max');
        $availableFormats = config('storage.audio.supported_formats');

        if (!preg_match("~^{$availableFormats}$~i", $extension)) {
            throw new ControllerException("Audio file has unsupported format.");
        }

        if ($copy = $this->getSameTrack($hash)) {
            throw new ControllerException("Audio file already exists in your library");
        }

        $uploadTimeLeft = $currentPlan->getTimeMax() - $this->user->getTracksDuration() - $duration;

        if ($duration > $maximalDuration) {
            throw new ControllerException("Audio file duration is too long.");
        }

        if ($duration < $currentPlan->getMinTrackLength()) {
            throw new ControllerException("Audio file duration is too short.");
        }

        if ($uploadTimeLeft < $duration) {
            throw new ControllerException("You don't have enough of time on your account to upload this audio file");
        }

        $track = new Track();

        $track->setUserID($this->user->getID());
        $track->setFileName($file["name"]);
        $track->setHash($hash);
        $track->setExtension($extension);
        $track->setTrackNumber($metadata["track_number"] ?? "");
        $track->setArtist($metadata["artist"] ?? "");
        $track->setTitle($metadata["title"] ?? $basename);
        $track->setAlbum($metadata["album"] ?? "");
        $track->setGenre($metadata["genre"] ?? "");
        $track->setDate($metadata["date"] ?? "");
        $track->setDuration($duration);
        $track->setFileSize($file["size"]);
        $track->setUploaded(time());
        $track->setColor(0);
        $track->setCopyOf(null);

        try {
            $file_id = FSFile::registerLink($file["tmp_name"], $extension, $hash);
            $track->setFileId($file_id);
            $track->save();
        } catch (LocalFileNotFoundException $exception) {
            throw new ControllerException(I18n::tr("UPLOAD_FILE_BROKEN", [$file["name"]]));
        } catch (NoSpaceForUploadException $exception) {
            throw new ControllerException(I18n::tr("UPLOAD_NO_SERVERS", [$file["name"]]));
        }

        $uniqueId = $this->addToStream($track, $addToStream, $upNext);

        error_log(sprintf("User #%d uploaded new track: %s (upload time left: %d seconds)",
            $track->getUserID(), $track->getFileName(), $uploadTimeLeft / 1000));

        $uploadedTrack = Playlist::getInstance()->getOneTrack($track->getID());

        $uniqueId->then(function ($uniqueId) use (&$uploadedTrack) {
            $uploadedTrack['unique_id'] = $uniqueId;
        });

        return $uploadedTrack;
    }

    /**
     * @param $hash
     * @return bool
     */
    public function getSameTrack($hash)
    {

        /** @var Track $copy */
        $copy = Track::getByFilter("hash = ? AND uid = ?", [$hash, $this->user->getID()])->getOrElseNull();

        if (is_null($copy)) {
            return $copy;
        } else {
            return Playlist::getInstance()->getOneTrack($copy->getID());
        }

    }

    private function addToStream(Track $track, Optional $stream, $upNext = false)
    {
        return $stream->map(function ($stream_id) use ($track, $upNext) {
            $uniqueIds = [];
            (new PlaylistModel($stream_id))->addTracks($track->getID(), $upNext, $uniqueIds);
            return $uniqueIds[0];
        });

    }

    /**
     * @param $trackId
     * @param null|Optional $destinationStream
     * @param bool $upNext
     * @return mixed
     * @throws ControllerException
     */
    public function copy($trackId, Optional $destinationStream = null, $upNext = false)
    {
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
     * @param $tracks
     */
    public function delete($tracks)
    {

        $tracksArray = explode(",", $tracks);
        foreach ($tracksArray as $track) {
            try {
                $track = new TrackModel($track);
                $track->delete();
            } catch (ControllerException $e) {
                error_log($e->getMyMessage());
            }
        }

    }

    /**
     * @param $tracks
     */
    public function deleteFromStreams($tracks)
    {

        $db = DBQuery::getInstance();

        $streams = $db->selectFrom("r_link")
            ->select("stream_id")
            ->selectAlias("GROUP_CONCAT(unique_id)", "unique_ids")
            ->where("FIND_IN_SET(track_id, ?)", [$tracks])
            ->addGroupBy("stream_id")->fetchAll();

        foreach ($streams as $stream) {

            $model = new PlaylistModel($stream['stream_id']);
            $model->removeTracks($stream['unique_ids']);

        }

    }


}