<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Model;


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

        Database::doInConnection(function (Database $db) use ($file, $audioTags, $addToStream, $duration) {

            $extension = pathinfo($file["name"], PATHINFO_EXTENSION);

            $query = $db->getDBQuery()->insertInto("r_tracks")->values([
                "uid"           => $this->user->getId(),
                "filename"      => $file["name"],
                "ext"           => $extension,
                "track_number"  => $audioTags["TRACKNUMBER"]    ->getOrElseEmpty(),
                "artist"        => $audioTags["PERFORMER"]      ->getOrElseEmpty(),
                "title"         => $audioTags["TITLE"]          ->getOrElse($file['name']),
                "album"         => $audioTags["ALBUM"]          ->getOrElseEmpty(),
                "genre"         => $audioTags["GENRE"]          ->getOrElseEmpty(),
                "date"          => $audioTags["RECORDED_DATE"]  ->getOrElseEmpty(),
                "duration"      => $duration,
                "filesize"      => filesize($file["tmp_name"]),
                'uploaded'      => time()
            ]);

            $id = $db->executeInsert($query);

            $track = new Track($id);

            $result = move_uploaded_file($file['tmp_name'], $track->getOriginalFile());

            if ($result !== false) {

                $db->commit();

                $addToStream->then(function ($streamID) use ($track) {

                    $streamObject = new StreamTrackList($streamID);
                    $streamObject->addTracks($track->getId());

                });


            } else {

                $db->rollback();

                throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

            }

        });

    }

} 