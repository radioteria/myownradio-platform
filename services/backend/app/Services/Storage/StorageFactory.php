<?php

namespace app\Services\Storage;

class StorageFactory
{
    /**
     * @return Storage
     */
    public static function getStorage()
    {
        return new LocalStorage(config('storage.local.dir'), function ($key) {
            return sprintf("https://fs1.myownradio.biz/${key}");
        });
    }
}
