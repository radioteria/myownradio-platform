<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 9:06
 */

namespace Framework\FileServer;


use Framework\FileServer\Exceptions\FileServerErrorException;
use Framework\FileServer\Exceptions\FileServerException;
use Framework\FileServer\Exceptions\FileServerUnreachableException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Framework\FileServer\Exceptions\NoSpaceForUploadException;
use Framework\FileServer\Exceptions\RemoteFileNotFoundException;
use Framework\FileServer\Exceptions\ServerNotRegisteredException;
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServer;

class FileServerFacade {

    const FS_PATTERN = "https://fs%d.radioter.io/";
    const FS_RETRY_TIMES = 2;

    private $fs_id;
    private $fs_object;

    function __construct($fs_id) {
        $this->fs_object = FileServer::getByID($fs_id)
            ->getOrElseThrow(new ServerNotRegisteredException(
                I18n::tr("FS_DOES_NOT_EXIST", ["id" => $fs_id])
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
     * Returns instance of available server with has needed amount
     * of free space.
     * @param $need_bytes
     * @throws NoSpaceForUploadException
     * @return FileServerFacade
     */
    public static function allocate($need_bytes) {
        $servers = self::getUpServersIds();
        foreach ($servers as $server) {
            $fs = new self($server);
            $free = self::doTwice(function () use ($fs) {
                return $fs->getFreeSpace();
            });
            if ($free === null) {
                continue;
            }
            if ($free > $need_bytes) {
                return $fs;
            }
        }
        throw new NoSpaceForUploadException(I18n::tr("FS_NO_FREE_SPACE", ["amount" => $need_bytes]));
    }

    /**
     * @param $callable
     * @return mixed
     * @throws FileServerException
     */
    public static function doTwice($callable) {

        $count = self::FS_RETRY_TIMES;

        while ($count --) {
            try {
                error_log("Trying command...");
                $result = call_user_func($callable);
                return $result;
            } catch (FileServerException $exception) {
                error_log("FileServerException: " . $exception->getMessage());
            }
        }

        return null;

    }

    /**
     * @param $file_path
     * @param null $hash
     * @throws Exceptions\FileServerUnreachableException
     * @throws Exceptions\FileServerErrorException
     * @throws Exceptions\LocalFileNotFoundException
     * @return mixed|null
     */
    public function uploadFile($file_path, $hash = null) {

        if (!file_exists($file_path)) {
            throw new LocalFileNotFoundException(I18n::tr("CMN_FILE_NOT_FOUND", ["name" => $file_path]));
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

        if ($result === false) {
            throw new FileServerUnreachableException();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code != 201) {
            throw new FileServerErrorException("Server response code: " . $http_code);
        }

        return $result;

    }

    /**
     * @param $hash
     * @throws Exceptions\FileServerUnreachableException
     * @throws Exceptions\FileServerErrorException
     * @return bool
     */
    public function delete($hash) {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName().$hash);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");

        $result = curl_exec($ch);

        if ($result === false) {
            throw new FileServerUnreachableException();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code != 200) {
            throw new FileServerErrorException("Server response code: " . $http_code);
        }

    }

    /**
     * @param $hash
     * @throws Exceptions\FileServerUnreachableException
     * @throws Exceptions\FileServerErrorException
     * @return bool
     */
    public function isFileExists($hash) {

        try {
            $this->getFileSize($hash);
        } catch (RemoteFileNotFoundException $exception) {
            return false;
        }

        return true;

    }

    /**
     * @param $hash
     * @throws Exceptions\FileServerUnreachableException
     * @throws Exceptions\FileServerErrorException
     * @throws Exceptions\RemoteFileNotFoundException
     * @return bool
     */
    public function getFileSize($hash) {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName().$hash);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "SIZE");

        $result = curl_exec($ch);

        if ($result === false) {
            throw new FileServerUnreachableException();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code == 404) {
            throw new RemoteFileNotFoundException("File not found: " . $hash);
        } else if ($http_code != 200) {
            throw new FileServerErrorException("Server response code: " . $http_code);
        }

        return $result;

    }

    /**
     * @return mixed
     * @throws Exceptions\FileServerUnreachableException
     * @throws Exceptions\FileServerErrorException
     */
    public function getFreeSpace() {

        $ch = $this->curlInit();

        curl_setopt($ch, CURLOPT_URL, $this->getServerName());
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "STAT");

        $result = curl_exec($ch);

        if ($result === false) {
            throw new FileServerUnreachableException();
        }

        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($http_code !== 200) {
            throw new FileServerErrorException("Server response code: " . $http_code);
        }

        return $result;

    }

    /**
     * @return string
     */
    private function getServerName() {
        return sprintf(self::FS_PATTERN, $this->fs_id);
    }

    /**
     * @param $server_id
     * @return string
     */
    public static function getServerNameById($server_id) {
        return sprintf(self::FS_PATTERN, $server_id);
    }

    /**
     * @return resource
     */
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
