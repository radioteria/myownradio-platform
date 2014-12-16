<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Model;


use FluentPDO;
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
    public function createStream($name, $info, $hashtags, $category, $permalink) {
        $id = $this->db->executeInsert(
            "INSERT INTO r_streams (uid, name, info, hashtags, category, permalink) VALUES (?, ?, ?, ?, ?, ?)",
            [User::getInstance()->getId(), $name, $info, $hashtags, $category, $permalink]);

        return Streams::getInstance()->getOneStream($id);
    }

    public function deleteStream($id) {
        $result = $this->db->executeUpdate("DELETE FROM r_streams WHERE sid = ? AND uid = ?",
            [$id, User::getInstance()->getId()]);

        if ($result === 0) {
            throw new ControllerException("Stream not found or no permission");
        } else {
            StreamTrackList::notifyAllStreamers($id);
        }
    }

    public function uploadFile(array $file, Optional $addToStream) {

        $user = AuthorizedUser::getInstance();
        $config = Config::getInstance();
        $database = Database::getInstance();

        // Check file type is supported
        if(array_search($file['type'], $config->getSetting('upload', 'supported_audio')->getOrElse([])) === false) {
            throw new ControllerException("Unsupported type of file: " . $file["type"]);
        }

        $audioTags = Common::getAudioTags($file['tmp_name']);

        if(empty($audioTags['DURATION']) || $audioTags['DURATION'] == 0) {
            throw new ControllerException("Uploaded file has no duration");
        }

        if($audioTags['DURATION'] > $config->getSetting('upload', 'maximal_length')->getOrElseThrow(
                ApplicationException::of("MAXIMAL TRACK DURATION NOT SPECIFIED"))
        ) {
            throw new ControllerException("Uploaded file is too long: " . $audioTags["DURATION"]);
        }

        $database->beginTransaction();

        $id = $database->executeInsert($database->createQuery(
            function(FluentPDO $fpdo) use ($file, $audioTags, $user) {
                $extension = pathinfo($file["name"], PATHINFO_EXTENSION);
                $query = $fpdo->insertInto("r_tracks")->values([
                    "uid"           => $user->getId(),
                    "filename"      => $file["name"],
                    "ext"           => $extension,
                    "track_number"  => Optional::ofEmpty($audioTags["TRACKNUMBER"])    ->getOrElseEmpty(),
                    "artist"        => Optional::ofEmpty($audioTags["PERFORMER"])      ->getOrElseEmpty(),
                    "title"         => Optional::ofEmpty($audioTags["TITLE"])          ->getOrElse($file['name']),
                    "album"         => Optional::ofEmpty($audioTags["ALBUM"])          ->getOrElseEmpty(),
                    "genre"         => Optional::ofEmpty($audioTags["GENRE"])          ->getOrElseEmpty(),
                    "date"          => Optional::ofEmpty($audioTags["RECORDED_DATE"])  ->getOrElseEmpty(),
                    "duration"      => $audioTags["DURATION"],
                    "filesize"      => filesize($file["tmp_name"]),
                    'uploaded'      => time()
                ]);
                return $query;
            }));

        $track = new Track($id);

        $result = move_uploaded_file($file['tmp_name'], $track->getOriginalFile());

        if($result) {

            $database->commit();

            $addToStream->then(function ($streamID) use ($track) {

                $streamObject = new StreamTrackList($streamID);
                $streamObject->addTracks($track->getId());

            });


        } else {

            $database->rollback();

            throw ApplicationException::of("FILE COULD NOT BE MOVED TO USER FOLDER");

        }

    }

} 