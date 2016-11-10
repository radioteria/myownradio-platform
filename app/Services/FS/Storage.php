<?php

namespace app\Services\FS;

abstract class Storage implements StorageInterface
{
    /**
     * @var callable|null
     */
    private $urlMapper;

    /**
     * @param callable|null $urlMapper
     */
    public function __construct(callable $urlMapper = null)
    {
        $this->urlMapper = $urlMapper;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws StorageException
     */
    public function url($key)
    {
        if (is_null($this->urlMapper)) {
            throw new StorageException("Url mapper not configured");
        }
        return call_user_func($this->urlMapper, $key);
    }
}
