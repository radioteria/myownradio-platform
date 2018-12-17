<?php

namespace app\Services\Storage;

class StorageFactory
{
    /**
     * @return Storage
     */
    public static function getStorage()
    {
        return new S3Storage();
    }
}
