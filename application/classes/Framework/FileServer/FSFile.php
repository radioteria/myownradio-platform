<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 11:59
 */

namespace Framework\FileServer;


use Framework\FileServer\Exceptions\FileServerException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Objects\FileServer\FileServerFile;

class FSFile {
    /**
     * @param $file_path
     * @param string|null $hash
     * @throws Exceptions\FileServerException
     * @throws Exceptions\LocalFileNotFoundException
     * @return int Created file ID
     */
    public static function registerLink($file_path, $hash = null) {

        if (!file_exists($file_path)) {
            throw new LocalFileNotFoundException(
                sprintf("File \"%s\" not exists", $file_path)
            );
        }

        if ($hash === null) {
            $hash = hash_file("sha512", $file_path);
        }


        /** @var FileServerFile $object */
        $object = FileServerFile::getByFilter("HASH", [$hash])->getOrElseNull();

        if ($object === null) {
            $size = filesize($file_path);
            $fs = FileServerFacade::allocate($size);

            $object = new FileServerFile();
            $object->setFileHash($hash);
            $object->setFileSize($size);
            $object->setServerId($fs->getServerId());
            $object->setUseCount(1);

            if (!$fs->isFileExists($hash) && $fs->uploadFile($file_path, $hash) === null) {
                throw new FileServerException(sprintf("File \"%s\" could not be uploaded", $file_path));
            }

        } else {
            $object->setUseCount($object->getUseCount() + 1);
        }

        $object->save();

        return $object->getFileId();

    }

    /**
     * @param $file_id
     * @throws Exceptions\FileServerException
     */
    public static function deleteLink($file_id) {

        /** @var FileServerFile $object */
        if ($object = FileServerFile::getByID($file_id)->getOrElseNull()) {
            if ($object->getUseCount() > 1) {
                $object->setUseCount($object->getUseCount() - 1);;
                $object->save();
            } else {
                $fs = new FileServerFacade($object->getServerId());
                if ($fs->delete($object->getFileHash())) {
                    $object->delete();
                }
            }
        }

    }
} 