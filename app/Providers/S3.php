<?php

namespace app\Providers;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Tools\Singleton;

class S3
{
    use Singleton;

    /**
     * @var S3Client
     */
    private $s3Client;

    /**
     * @var mixed
     */
    private $bucket;

    public function __construct()
    {
        $credentials = new Credentials(
            config('services.s3.access_key'),
            config('services.s3.secret_key')
        );

        $this->s3Client = new S3Client([
            'region'            => config('services.s3.region'),
            'version'           => 'latest',
            'signature_version' => config('service.s3.signature_version'),
            'credentials'       => $credentials
        ]);

        $this->bucket = config('services.s3.bucket');
    }

    /**
     * @return S3Client
     */
    public function getS3Client()
    {
        return $this->s3Client;
    }

    /**
     * @param $key
     * @return bool
     */
    public function doesObjectExist($key)
    {
        return $this->getS3Client()->doesObjectExist($this->bucket, $key);
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key)
    {
        $result = $this->getS3Client()->getObject([
            'Bucket'       => $this->bucket,
            'Key'          => $key,
        ]);

        return $result['Body'];
    }

    /**
     * @param $key
     * @param $body
     * @param null|string $contentType
     */
    public function put($key, $body, $contentType = null)
    {
        $this->getS3Client()->putObject([
            'Bucket'       => $this->bucket,
            'Key'          => $key,
            'Body'         => $body,
            'ACL'          => 'public-read',
            'ContentType' => $contentType
        ]);
    }

    /**
     * @param $key
     * @return string
     */
    public function url($key)
    {
        return $this->getS3Client()->getObjectUrl($this->bucket, $key);
    }

    /**
     * @param $key
     */
    public function delete($key)
    {
        $this->getS3Client()->deleteObject([
            "Bucket"    => $this->bucket,
            "Key"       => $key
        ]);
    }
}
