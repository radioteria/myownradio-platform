<?php

namespace app\Providers;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Tools\Singleton;

class S3
{
    use Singleton;

    private $s3Client;

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

        $this->bucket = config('service.s3.bucket');
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
    public function doesObjectExists($key)
    {
        return $this->getS3Client()->doesObjectExist($this->bucket, $key);
    }

    /**
     * @param $key
     * @param $body
     */
    public function put($key, $body)
    {
        $this->getS3Client()->putObject([
            'Bucket' => config('services.s3.bucket'),
            'Key'    => $key,
            'Body'   => $body,
            'ACL'    => 'public-read'
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
}
