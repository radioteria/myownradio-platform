<?php

namespace Framework\FileServer;

use app\Services\Storage\StorageFactory;
use Framework\Defaults;
use Framework\FileServer\Exceptions\FileServerException;
use Framework\FileServer\Exceptions\LocalFileNotFoundException;
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServerFile;

class FSFile
{
    public static function getPathByHash($hash)
    {
        return $remoteFileName = sprintf('audio/%s/%s/%s', $hash[0], $hash[1], $hash);
    }

    /**
     * @param $file_path
     * @param string|null $hash
     * @throws Exceptions\LocalFileNotFoundException
     * @throws Exceptions\NoSpaceForUploadException
     * @return int Created file ID
     */
    public static function registerLink($file_path, $hash = null)
    {

        if (!file_exists($file_path)) {
            throw new LocalFileNotFoundException(I18n::tr("CMN_FILE_NOT_FOUND", ["name" => $file_path]));
        }

        if ($hash === null) {
            $hash = hash_file(Defaults::HASHING_ALGORITHM, $file_path);
        }


        /** @var FileServerFile $object */
        $object = FileServerFile::getByFilter("HASH", [$hash])->getOrElseNull();

        if (is_null($object)) {
            $filesize = filesize($file_path);

            $object = new FileServerFile();
            $object->setFileHash($hash);
            $object->setFileSize($filesize);
            $object->setServerId(1);
            $object->setUseCount(1);

            $storage = StorageFactory::getStorage();

            $storage->put(self::getPathByHash($hash), fopen($file_path, 'r'));
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
    public static function deleteLink($file_id)
    {
        FileServerFile::getByID($file_id)->then(function (FileServerFile $file) {
            if ($file->getUseCount() > 0) {
                $file->setUseCount($file->getUseCount() - 1);
                $file->save();
            }
            if ($file->getUseCount() < 1) {
                $storage = StorageFactory::getStorage();
                $storage->delete(self::getPathByHash($file->getFileHash()));
                $file->delete();
            }
        });
    }

    /**
     * @throws FileServerException
     */
    public static function deleteUnused()
    {
        $files = FileServerFile::getListByFilter("UNUSED");
        foreach ($files as $file) {
            try {
                $storage = StorageFactory::getStorage();
                $storage->delete(self::getPathByHash($file->getFileHash()));
            } catch (FileServerException $exception) {
                error_log($exception->getMessage());
            }
        }
    }
}
