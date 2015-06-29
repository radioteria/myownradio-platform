<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 19.12.14
 * Time: 23:33
 */

namespace Framework\Models;


use Objects\StreamTrack;
use Tools\Optional\Option;
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
     * @return Option
     */
    public function getByUniqueID($uniqueID) {
        return StreamTrack::getByID("unique_id = ? AND stream_id = ?", [$uniqueID, $this->key]);
    }

    /**
     * @param $order
     * @return Option
     */
    public function getByTrackOrder($order) {
        return StreamTrack::getByFilter("t_order = ? AND stream_id = ?", [$order, $this->key]);
    }

    /**
     * @return Option
     */
    public function getRandom() {
        return StreamTrack::getRandom($this->key);
    }

} 