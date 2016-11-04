<?php

namespace app\Providers;

use Aws\Credentials\Credentials;
use Aws\S3\S3Client;
use Tools\Singleton;

class S3ServiceProvider
{
    use Singleton;

    private $s3Client;

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
    }

    /**
     * @return S3Client
     */
    public function getS3Client()
    {
        return $this->s3Client;
    }
}
