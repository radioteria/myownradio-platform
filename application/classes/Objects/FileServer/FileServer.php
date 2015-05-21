<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 9:16
 */

namespace Objects\FileServer;


use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class FileServerList
 * @package Objects\FileServer
 *
 * @table fs_list
 * @key fs_id
 * @do_UP is_online = 1 AND is_enabled = 1
 */
class FileServer extends ActiveRecordObject implements ActiveRecord {

    private $fs_id;
    private $is_online = 1;
    private $is_enabled = 1;
    private $fs_host = "";
    private $files_count;

    /**
     * @return mixed
     */
    public function getFsId() {
        return $this->fs_id;
    }

    /**
     * @return int
     */
    public function getIsEnabled() {
        return $this->is_enabled;
    }

    /**
     * @return int
     */
    public function getIsOnline() {
        return $this->is_online;
    }

    /**
     * @param int $is_enabled
     */
    public function setIsEnabled($is_enabled) {
        $this->is_enabled = $is_enabled;
    }

    /**
     * @param int $is_online
     */
    public function setIsOnline($is_online) {
        $this->is_online = $is_online;
    }

    /**
     * @param string $fs_host
     */
    public function setFsHost($fs_host) {
        $this->fs_host = $fs_host;
    }

    /**
     * @return string
     */
    public function getFsHost() {
        return $this->fs_host;
    }

    /**
     * @return mixed
     */
    public function getFilesCount() {
        return $this->files_count;
    }


} 