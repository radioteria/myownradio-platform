<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 03.02.2015
 * Time: 23:22
 */

namespace Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class Playlist
 * @package Objects
 * @table mor_playlists
 * @key playlist_id
 */
class Playlist extends ActiveRecordObject implements ActiveRecord {
    protected
        $playlist_id,
        $user_id,
        $playlist_name;

    /**
     * @return mixed
     */
    public function getPlaylistId()
    {
        return $this->playlist_id;
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return mixed
     */
    public function getPlaylistName()
    {
        return $this->playlist_name;
    }

    /**
     * @param mixed $user_id
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
    }

    /**
     * @param mixed $playlist_name
     */
    public function setPlaylistName($playlist_name)
    {
        $this->playlist_name = $playlist_name;
    }


}