<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 11/10/16
 * Time: 3:12 PM
 */

namespace app\Services\FS;

class MemoryStorage extends Storage
{
    /**
     * @var array
     */
    private $files = [];

    /**
     * @param string $key
     * @return mixed
     * @throws StorageException
     */
    public function get($key)
    {
        if (!$this->exists($key)) {
            throw new StorageException("File \"{$key}\" does not exist");
        }
        return $this->files[$key];
    }

    /**
     * @param string $key
     * @param mixed $body
     * @param array $parameters
     */
    public function put($key, $body, array $parameters = [])
    {
        $this->files[$key] = $body;
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        unset($this->files[$key]);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        return array_key_exists($key, $this->files);
    }
}
