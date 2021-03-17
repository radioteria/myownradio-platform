<?php

namespace app;

use Framework\Exceptions\ApplicationException;
use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;

class Config implements Injectable, SingletonInterface
{
    use Singleton;

    private string $radioStreamerEndpoint;
    private string $radioStreamerToken;

    private string $facebookAppId;
    private string $facebookAppSecret;

    private function __construct(
        string $radioStreamerEndpoint,
        string $radioStreamerToken,
        string $facebookAppId,
        string $facebookAppSecret
    ) {
        $this->radioStreamerEndpoint = $radioStreamerEndpoint;
        $this->radioStreamerToken = $radioStreamerToken;

        $this->facebookAppId = $facebookAppId;
        $this->facebookAppSecret = $facebookAppSecret;
    }

    public static function fromEnv(): Config
    {
        $radioStreamerEndpoint = env("RADIO_STREAMER_ENDPOINT");
        $radioStreamerToken = env("RADIO_STREAMER_TOKEN");

        if ($radioStreamerEndpoint === null || $radioStreamerToken === null) {
            throw new ApplicationException(
                'Environment variables "RADIO_STREAMER_ENDPOINT" and "RADIO_STREAMER_TOKEN" are required for operation'
            );
        }

        $facebookAppId = env('FACEBOOK_APP_ID');
        $facebookAppSecret = env('FACEBOOK_APP_SECRET');

        if ($facebookAppId === null || $facebookAppSecret === null) {
            throw new ApplicationException(
                'Environment variables "FACEBOOK_APP_ID" and "FACEBOOK_APP_SECRET" are required for operation'
            );
        }

        return new static(
            $radioStreamerEndpoint,
            $radioStreamerToken,
            $facebookAppId,
            $facebookAppSecret
        );
    }

    public function getRadioStreamerEndpoint(): string
    {
        return $this->radioStreamerEndpoint;
    }

    public function getRadioStreamerToken(): string
    {
        return $this->radioStreamerToken;
    }

    public function getFacebookAppId(): string
    {
        return $this->facebookAppId;
    }

    public function getFacebookAppSecret(): string
    {
        return $this->facebookAppSecret;
    }
}
