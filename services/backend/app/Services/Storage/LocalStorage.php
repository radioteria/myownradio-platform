<?php

namespace app\Services\Storage;

class LocalStorage extends Storage
{
    /**
     * @var string
     */
    private string $root;

    /**
     * @var string
     */
    private string $separator = '/';

    /**
     * @var int
     */
    private int $directoryRights = 0777;

    /**
     * @param string $rootDir
     * @param callable|null $urlMapper
     */
    public function __construct(string $rootDir, callable $urlMapper = null)
    {
        $this->root = $rootDir;
        parent::__construct($urlMapper);
    }

    /**
     * @param string $key
     * @return string
     * @throws StorageException
     */
    public function get(string $key): string
    {
        $fullPath = $this->getFullPath($key);

        $results = glob($fullPath);

        if (count($results) === 0) {
            throw new StorageException("File with key \"$key\" does not exist");
        }

        return file_get_contents($results[0]);
    }

    /**
     * @param $key
     * @return mixed
     */
    private function getFullPath($key): string
    {
        return $this->root . $this->separator . $key;
    }

    /**
     * @param $key
     * @param $body
     * @param array $parameters
     * @throws StorageException
     */
    public function put(string $key, $body, array $parameters = [])
    {
        $fullPath = $this->getFullPath($key);
        $dirName = pathinfo($fullPath, PATHINFO_DIRNAME);

        if ($dirName != '.' && !is_dir($dirName)) {
            mkdir($dirName, $this->directoryRights, true);
        }

        $result = file_put_contents($fullPath, $body);

        if ($result === false) {
            throw new StorageException('Error occurred during saving file');
        }
    }

    /**
     * @param string $key
     * @throws StorageException
     */
    public function delete(string $key)
    {
        $fullPath = $this->getFullPath($key);

        if (!unlink($fullPath)) {
            throw new StorageException('Error occurred during deleting file');
        }
    }

    /**
     * @param string $key
     * @return bool
     */
    public function exists(string $key): bool
    {
        $fullPath = $this->getFullPath($key);

        return file_exists($fullPath);
    }
}
