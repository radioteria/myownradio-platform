<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 20.12.14
 * Time: 14:28
 */

namespace Framework\Services;


use Model\AuthUserModel;
use Model\StreamModel;
use Model\StreamsModel;
use Model\TrackModel;
use Model\TracksModel;
use Model\UserModel;
use Model\UsersModel;
use Tools\Singleton;

class Services {

    use Singleton, Injectable;

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