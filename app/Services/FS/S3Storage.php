<?php

namespace app\Services\FS;

use app\Providers\S3;

class S3Storage extends Storage
{
    /**
     * @var S3
     */
    private $s3;

    /**
     * @param S3 $s3
     */
    public function __construct(S3 $s3)
    {
        $this->s3 = $s3;
        parent::__construct([$this->s3, 'url']);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->s3->get($key);
    }

    /**
     * @param string $key
     * @param mixed $body
     * @param array $parameters
     */
    public function put($key, $body, array $parameters = [])
    {
        $contentType = isset($parameters['ContentType']) ? $parameters['ContentType'] : null;
        $this->s3->put($key, $body, $contentType);
    }

    /**
     * @param string $key
     */
    public function delete($key)
    {
        $this->s3->delete($key);
    }

    /**
     * @param string $key
     */
    public function exists($key)
    {
        $this->s3->doesObjectExist($key);
    }
}
