<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 12:03
 */

namespace Objects\FileServer;

use Framework\Services\ORM\EntityUtils\ActiveRecord;
use Framework\Services\ORM\EntityUtils\ActiveRecordObject;

/**
 * Class FileServerFile
 * @package Objects\FileServer
 * @table fs_file
 * @key file_id
 * @do_HASH file_hash = ?
 * @do_UNUSED use_count = 0
 */
class FileServerFile extends ActiveRecordObject implements ActiveRecord
{
    private $file_id;
    private $file_size;
    private $file_hash;
    private string $file_extension;
    private $server_id;
    private $use_count;

    /**
     * @return mixed
     */
    public function getFileHash()
    {
        return $this->file_hash;
    }

    /**
     * @param mixed $file_hash
     */
    public function setFileHash($file_hash)
    {
        $this->file_hash = $file_hash;
    }

    /**
     * @return mixed
     */
    public function getFileId()
    {
        return $this->file_id;
    }

    /**
     * @return mixed
     */
    public function getFileSize()
    {
        return $this->file_size;
    }

    /**
     * @param mixed $file_size
     */
    public function setFileSize($file_size)
    {
        $this->file_size = $file_size;
    }

    /**
     * @return mixed
     */
    public function getServerId()
    {
        return $this->server_id;
    }

    /**
     * @param mixed $server_id
     */
    public function setServerId($server_id)
    {
        $this->server_id = $server_id;
    }

    /**
     * @return FileServer|null
     */
    public function getServerObject()
    {
        return FileServer::getByID($this->server_id)->getOrElseNull();
    }

    /**
     * @return mixed
     */
    public function getUseCount()
    {
        return $this->use_count;
    }

    /**
     * @param mixed $use_count
     */
    public function setUseCount($use_count)
    {
        $this->use_count = $use_count;
    }

    /**
     * @return string
     */
    public function getFileExtension(): string
    {
        return $this->file_extension;
    }

    /**
     * @param string $file_extension
     */
    public function setFileExtension(string $file_extension): void
    {
        $this->file_extension = $file_extension;
    }
}
