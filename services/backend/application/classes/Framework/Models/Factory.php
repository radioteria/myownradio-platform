<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 14.12.14
 * Time: 15:57
 */

namespace Framework\Models;


use Framework\Exceptions\ControllerException;
use Framework\Exceptions\UnauthorizedException;
use Framework\Injector\Injectable;
use Framework\Services\DB\Query\DeleteQuery;
use Framework\Services\DB\Query\SelectQuery;
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

        $uid = (new SelectQuery("r_streams"))->where("sid", $id)->select("uid")->fetchOneColumn()
            ->getOrElseThrow(ControllerException::noStream($id));

        if ($uid != $this->user->getID()) {
            throw UnauthorizedException::noPermission();
        }

        (new DeleteQuery("r_link"))->where("stream_id", $id)->update();
        (new DeleteQuery("r_streams"))->where("sid", $id)->update();

        PlaylistModel::notifyAllStreamers($id);

    }


} 