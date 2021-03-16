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

    private function __construct(string $radioStreamerEndpoint, string $radioStreamerToken)
    {
        $this->radioStreamerEndpoint = $radioStreamerEndpoint;
        $this->radioStreamerToken = $radioStreamerToken;
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

        return new static(
            $radioStreamerEndpoint,
            $radioStreamerToken
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
}
