<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 23:33
 */

namespace Framework\Models;


use Objects\StreamTrack;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

/**
 * Class PlaylistTracksModel
 * @package Framework\Models
 * @localized 21.05.2015
 */
class PlaylistTracksModel implements SingletonInterface {

    use Singleton;

    /** @var UserModel $user */
    /** @var StreamModel $stream */

    protected $user;
    protected $key;
    protected $stream;

    public function __construct($id) {
        $this->user = AuthUserModel::getInstance();
        $this->key = $id;
    }

    /**
     * @param $uniqueID
     * @return Optional
     */
    public function getByUniqueID($uniqueID) {
        return StreamTrack::getByID("unique_id = ? AND stream_id = ?", [$uniqueID, $this->key]);
    }

    /**
     * @param $order
     * @return Optional
     */
    public function getByTrackOrder($order) {
        return StreamTrack::getByFilter("t_order = ? AND stream_id = ?", [$order, $this->key]);
    }

    /**
     * @return Optional
     */
    public function getRandom() {
        return StreamTrack::getRandom($this->key);
    }

} 