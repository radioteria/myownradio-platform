<?php

namespace app\Services\Storage;

use app\Config\Config;
use ReflectionException;

class StorageFactory
{
    /**
     * @return StorageInterface
     * @throws ReflectionException
     */
    public static function getStorage(): StorageInterface
    {
        $config = Config::getInstance();

        switch ($config->getStorageBackend()) {
            case 's3':
                return new S3Storage();

            case 'local':
            default: {
                $fileServerOwnAddress = $config->getFileServerOwnAddress();

                return new LocalStorage(config('storage.local.dir'), function ($key) use (&$fileServerOwnAddress) {
                    return "${fileServerOwnAddress}/${key}";
                });
            }
        }
    }
}
