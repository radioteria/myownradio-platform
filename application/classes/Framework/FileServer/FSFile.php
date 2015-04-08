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
use Framework\Services\Locale\I18n;
use Objects\FileServer\FileServerFile;

class FSFile {

    /**
     * @param $file_path
     * @param string|null $hash
     * @throws Exceptions\LocalFileNotFoundException
     * @throws Exceptions\NoSpaceForUploadException
     * @return int Created file ID
     */
    public static function registerLink($file_path, $hash = null) {

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
            $fs = FileServerFacade::allocate($filesize);

            $object = new FileServerFile();
            $object->setFileHash($hash);
            $object->setFileSize($filesize);
            $object->setServerId($fs->getServerId());
            $object->setUseCount(1);

            if (!$fs->isFileExists($hash)) {
                $fs->uploadFile($file_path, $hash);
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
                if ($object->getUseCount() > 0) {
                    $object->setUseCount($object->getUseCount() - 1);
                    $object->save();
                } else {
                }
            }

            $db->commit();

        });

    }

    public static function deleteUnused() {
        $files = FileServerFile::getListByFilter("UNUSED");
        foreach ($files as $file) {
            $fs = new FileServerFacade($file->getServerId());
            try {
                $fs->delete($file->getFileHash());
                $file->delete();
            } catch (FileServerException $exception) {
                error_log($exception->getMessage());
            }
        }
    }

}