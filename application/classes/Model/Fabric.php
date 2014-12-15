<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Model;


use MVC\Exceptions\ControllerException;
use MVC\Services\Injectable;
use REST\Streams;
use Tools\Singleton;

class Fabric extends Model {
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
            [User::getInstance()->getId(), $name, $info, $hashtags, $category, $permalink])
            ->getOrElseThrow(ControllerException::databaseError("CREATE STREAM"));

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

    public function uploadFile($file, $addToStream = null) {
        // todo: do this 16/12/2014
    }

} 