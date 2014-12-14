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
use Tools\Singleton;

class Fabric extends Model {
    use Singleton, Injectable;

    /**
     * @param string $name
     * @param string $info
     * @param string $hashtags
     * @param int|null $category
     * @param string|null $permalink
     * @param int $creator
     * @return Stream
     * @throws ControllerException
     */
    public function createStream($name, $info, $hashtags, $category, $permalink, $creator) {
        $id = $this->db->executeInsert(
            "INSERT INTO r_streams (uid, name, info, hashtags, category, permalink) VALUES (?, ?, ?, ?, ?, ?)",
            [$creator, $name, $info, $hashtags, $category, $permalink])
            ->getOrElseThrow(ControllerException::databaseError());

        return Stream::getInstance($id);
    }

} 