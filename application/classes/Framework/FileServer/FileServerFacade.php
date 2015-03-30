<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 9:06
 */

namespace Framework\FileServer;


use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Framework\FileServer\Exceptions\NoSpaceForUploadException;
use Framework\FileServer\Exceptions\ServerNotRegisteredException;
use Objects\FileServer\FileServer;

class FileServerFacade {

    const FS_PATTERN = "http://fs%d.myownradio.biz/";

    private $fs_id;
    private $fs_object;

    function __construct($fs_id) {
        $this->fs_object = FileServer::getByID($fs_id)
            ->getOrElseThrow(new ServerNotRegisteredException(
                sprintf("File server with id=%d is not registered!", $fs_id)
            ));
        $this->fs_id = $fs_id;
    }

    public static function getUpServersIds() {
        $servers = [];
        foreach (FileServer::getListByFilter("UP", null, null, null, "RAND()") as $server) {
            $servers[] = $server->getFsId();
        }
        return $servers;
    }

    /**
     * @param $need_bytes
     * @throws NoSpaceForUploadException
     * @return FileServerFacade
     */
    public static function allocate($need_bytes) {
        $servers = self::getUpServersIds();
        foreach ($servers as $server) {
            $fs = new self($server);
            $free = $fs->getFreeSpace();
            if ($free === null) {
                error_log("Warning! File server responded an error!");
                continue;
            }
            if ($free > $need_bytes) {
                return $fs;
            }
        }
        throw new NoSpaceForUploadException("There is no available servers for upload");
    }

    public function uploadFile($file_path, $hash = null) {

        if (!file_exists($file_path)) {
            throw new LocalFileNotFoundException(
                sprintf("File \"%s\" not found", $file_path)
            );
        }

        $ch = $this->curlInit();

        $post = [
            "file" => curl_file_create($file_path, "application/octet-stream")
        ];

        if ($hash !== null) {
            $post["hash"] = $hash;
        }

        curl_setopt($ch, CURLOPT_URL, $this->getServerName());
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $result = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code !== 201) {
            return null;
        } else {
            return $result;
        }

    }

    public function delete($hash) {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName().$hash);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $http_code === 200;

    }

    public function isFileExists($hash) {

        return $this->getFileSize($hash) !== null;

    }

    public function getFileSize($hash) {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName().$hash);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "SIZE");

        $result = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $http_code === 200 ? $result : null;

    }

    public function getFreeSpace() {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "STAT");

        $result = curl_exec($ch);

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return $http_code === 200 ? (int) $result : null;

    }

    private function getServerName() {
        return sprintf(self::FS_PATTERN, $this->fs_id);
    }

    public static function getServerNameById($server_id) {
        return sprintf(self::FS_PATTERN, $server_id);
    }

    private function curlInit() {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 60);
        return $curl;

    }

    /**
     * @return mixed
     */
    public function getServerId() {
        return $this->fs_id;
    }


} 