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
use Framework\Services\Database;
use Objects\FileServer\FileServerFile;

class FSFile {

    /**
     * @param $filename
     * @param string|null $hash
     * @throws Exceptions\LocalFileNotFoundException
     * @throws Exceptions\NoSpaceForUploadException
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

            if (!$fs->isFileExists($hash)) {
                $fs->uploadFile($filename, $hash);
            }

        } else {
            $object->setUseCount($object->getUseCount() + 1);
        }

        $object->save();

        return $object->getFileId();

    }

    /**
     * @param $file_id
     * @internal FileServerFile $object
     */
    public static function deleteLink($file_id) {

        Database::doInConnection(function (Database $db) use ($file_id) {

            $db->beginTransaction();

            if ($object = FileServerFile::getByID($file_id)->getOrElseNull()) {
                if ($object->getUseCount() > 1) {
                    $object->setUseCount($object->getUseCount() - 1);
                    $object->save();
                } else {
                    $fs = new FileServerFacade($object->getServerId());
                    try {
                        $fs->delete($object->getFileHash());
                        $object->delete();
                    } catch (FileServerException $exception) {
                        $object->setUseCount(0);
                        $object->save();
                    }
                }
            }

            $db->commit();

        });

    }
} 