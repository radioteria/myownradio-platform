<?php

namespace app\Services\Storage;

class StorageFactory
{
    /**
     * @return Storage
     */
    public static function getStorage()
    {
        $config = \app\Config\Config::getInstance();
        $fileServerOwnAddress = $config->getFileServerOwnAddress();

        return new LocalStorage(config('storage.local.dir'), function ($key) use (&$fileServerOwnAddress) {
            return "${fileServerOwnAddress}/${key}";
        });
    }
}
