<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Model;


use Model\Beans\TrackAR;
use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\Config;
use MVC\Services\Database;
use MVC\Services\Injectable;
use REST\Streams;
use Tools\Common;
use Tools\Optional;
use Tools\Singleton;

class Factory extends Model {

    use Singleton, Injectable;

    /**
     * @param string $name
     * @param string $info
     * @param string $hashtags
     * @param int|null $category
     * @param string|null $permalink
     * @return array
     * @throws ControllerException
     */

    /** @var User */
    private $user;

    function __construct() {
        $this->user = AuthorizedUser::getInstance();
    }


    public function createStream($name, $info, $hashtags, $category, $permalink) {



        $id = Database::doInConnection(function (Database $db)
                                        use ($name, $info, $hashtags, $category, $permalink) {

            $query = $db->getDBQuery()->insertInto("r_streams");
            $query->values([
                "uid"       => $this->user->getId(),
                "name"      => $name,
                "info"      => $info,
                "hashtags"  => $hashtags,
                "category"  => $category,
                "permalink" => $permalink,
                "created"   => time()
            ]);

            $uid = $db->executeInsert($query);

            $db->commit();

            return $uid;

        });

        return Streams::getInstance()->getOneStream($id);

    }

    public function deleteStream($id) {

        Database::doInConnection(function (Database $db) use ($id) {

            $result = $db->executeUpdate("DELETE FROM r_streams WHERE sid = ? AND uid = ?",
                [$id, $this->user->getId()]);

            if ($result === 0) {
                throw new ControllerException("Stream not found or no permission");
            } else {
                StreamTrackList::notifyAllStreamers($id);
            }

        });

    }

    public function uploadFile(array $file, Optional $addToStream) {

        $config = Config::getInstance();

        if(array_search($file['type'], $config->getSetting('upload', 'supported_audio')->getOrElse([])) === false) {
            throw new ControllerException("Unsupported type of file: " . $file["type"]);
        }

        $audioTags = Common::getAudioTags($file['tmp_name']);

        $maximalDuration = $config->getSetting('upload', 'maximal_length')->getOrElseThrow(
            ApplicationException::of("MAXIMAL TRACK DURATION NOT SPECIFIED"));

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

        $track = new TrackAR();

        $track->setUserID($this->user->getId());
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

                $streamObject = new StreamTrackList($streamID);
                $streamObject->addTracks($track->getID());

            });


        } else {

            $track->delete();

            throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

        }

    }

} 