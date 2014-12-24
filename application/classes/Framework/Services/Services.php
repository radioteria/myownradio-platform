<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:28
 */

namespace Framework\Services;


use Framework\Models\AuthUserModel;
use Framework\Models\StreamModel;
use Framework\Models\StreamsModel;
use Framework\Models\TrackModel;
use Framework\Models\TracksModel;
use Framework\Models\UserModel;
use Framework\Models\UsersModel;
use Tools\Singleton;

class Services implements Injectable {

    use Singleton;

    /**
     * @param $id
     * @return StreamModel
     */
    function getStream($id) {
        return StreamModel::getInstance($id);
    }

    /**
     * @return StreamsModel
     */
    function getStreams() {
        return StreamsModel::getInstance();
    }

    /**
     * @param $id
     * @return UserModel
     */
    function getUserModel($id) {
        return UserModel::getInstance($id);
    }

    /**
     * @return UsersModel
     */
    function getUsersModel() {
        return UsersModel::getInstance();
    }

    /**
     * @param $id
     * @return TrackModel
     */
    function getTrackModel($id) {
        return TrackModel::getInstance($id);
    }

    /**
     * @return TracksModel
     */
    function getTracksModel() {
        return TracksModel::getInstance();
    }

    /**
     * @return UserModel
     */
    function getAuthUser() {
        return AuthUserModel::getInstance();
    }
} 