<?php
/**
 * Created by PhpStorm.
 * User: LRU
 * Date: 03.02.2015
 * Time: 23:25
 */

namespace application\classes\Objects;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class PlaylistLink
 * @package application\classes\Objects
 * @table mor_playlists_link
 * @key link_id
 */
class PlaylistLink extends ActiveRecordObject implements ActiveRecord {
    protected $link_id, $position_id, $playlist_id, $track_id;

    /**
     * @return mixed
     */
    public function getLinkId()
    {
        return $this->link_id;
    }

    /**
     * @return mixed
     */
    public function getPositionId()
    {
        return $this->position_id;
    }

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
    public function getTrackId()
    {
        return $this->track_id;
    }

    /**
     * @param mixed $position_id
     */
    public function setPositionId($position_id)
    {
        $this->position_id = $position_id;
    }

    /**
     * @param mixed $playlist_id
     */
    public function setPlaylistId($playlist_id)
    {
        $this->playlist_id = $playlist_id;
    }

    /**
     * @param mixed $track_id
     */
    public function setTrackId($track_id)
    {
        $this->track_id = $track_id;
    }


}