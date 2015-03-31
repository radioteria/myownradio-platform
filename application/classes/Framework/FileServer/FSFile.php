<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 30.03.15
 * Time: 11:59
 */

namespace Framework\FileServer;


use Framework\Defaults;
use Framework\FileServer\Exceptions\FileServerException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Objects\FileServer\FileServerFile;

class FSFile {

    /**
     * @param $filename
     * @param string|null $hash
     * @throws Exceptions\FileServerException
     * @throws Exceptions\LocalFileNotFoundException
     * @return int Created file ID
     */
    public static function registerLink($filename, $hash = null) {

        if (!file_exists($filename)) {
            throw new LocalFileNotFoundException(
                sprintf("File \"%s\" could not be found", $filename)
            );
        }

        if ($hash === null) {
            $hash = hash_file(Defaults::HASHING_ALGORITHM, $filename);
        }


        /** @var FileServerFile $object */
        $object = FileServerFile::getByFilter("HASH", [$hash])->getOrElseNull();

        if ($object === null) {
            $filesize = filesize($filename);
            $fs = FileServerFacade::allocate($filesize);

            $object = new FileServerFile();
            $object->setFileHash($hash);
            $object->setFileSize($filesize);
            $object->setServerId($fs->getServerId());
            $object->setUseCount(1);

            if (!$fs->isFileExists($hash) && $fs->uploadFile($filename, $hash) === null) {
                throw new FileServerException(sprintf("File \"%s\" could not be uploaded", $filename));
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