<?php

namespace app\Services\Storage;

use app\Providers\S3;

class StorageFactory
{
    /**
     * @return Storage
     */
    public static function getStorage()
    {
        return new S3Storage(S3::getInstance());
    }
}
