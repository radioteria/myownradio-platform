<?php

namespace app\Services\Storage;

class LocalStorage extends Storage
{
    /**
     * @var string
     */
    private $root;

    /**
     * @var string
     */
    private $separator = '/';

    /**
     * @var int
     */
    private $directoryRights = 0777;

    /**
     * @param string $rootDir
     * @param callable|null $urlMapper
     */
    public function __construct($rootDir, callable $urlMapper = null)
    {
        $this->root = $rootDir;
        parent::__construct($urlMapper);
    }

    /**
     * @param string $key
     * @return string
     * @throws StorageException
     */
    public function get($key)
    {
        return file_get_contents($key);
    }

    /**
     * @param $key
     * @param $body
     * @param array $parameters
     */
    public function put($key, $body, array $parameters = [])
    {
        $fullPath = $this->getFullPath($key);
        $dirName = pathinfo($fullPath, PATHINFO_DIRNAME);

        if ($dirName != '.') {
            mkdir($dirName, $this->directoryRights, true);
        }

        file_put_contents($fullPath, $body);
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $fullPath = $this->getFullPath($key);

        unlink($fullPath);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists($key)
    {
        $fullPath = $this->getFullPath($key);

        return file_exists($fullPath);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getFullPath($key)
    {
        return $this->root . $this->separator . $key;
    }
}
