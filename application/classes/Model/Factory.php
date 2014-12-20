<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Model;


use Framework\Exceptions\ControllerException;
use Framework\Services\Database;
use Framework\Services\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class Factory extends Model implements Injectable, SingletonInterface {

    use Singleton;

    /**
     * @param string $name
     * @param string $info
     * @param string $hashtags
     * @param int|null $category
     * @param string|null $permalink
     * @return array
     * @throws ControllerException
     */

    /** @var UserModel */
    private $user;

    function __construct() {
        $this->user = AuthUserModel::getInstance();
    }



    public function deleteStream($id) {

        Database::doInConnection(function (Database $db) use ($id) {

            $result = $db->executeUpdate("DELETE FROM r_streams WHERE sid = ? AND uid = ?",
                [$id, $this->user->getID()]);

            if ($result === 0) {
                throw new ControllerException("Stream not found or no permission");
            } else {
                PlaylistModel::notifyAllStreamers($id);
            }

        });

    }


} 