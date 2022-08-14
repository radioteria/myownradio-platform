<?php

namespace app\Config;

use Framework\Exceptions\ApplicationException;
use Framework\Injector\Injectable;
use Tools\Singleton;
use Tools\SingletonInterface;
use function env;

class Config implements Injectable, SingletonInterface
{
    use Singleton;

    private string $radioStreamerEndpoint;
    private string $radioStreamerInternalEndpoint;
    private string $radioStreamerToken;

    private string $facebookAppId;
    private string $facebookAppSecret;

    private string $smtpHost;
    private string $smtpUser;
    private string $smtpPassword;
    private int $smtpPort;

    private string $emailSenderEmail = "noreply@myownradio.biz";
    private string $emailSenderName = "myownradio.biz";

    private string $webServerOwnAddress;
    private string $fileServerOwnAddress;

    private function __construct(
        string $radioStreamerEndpoint,
        string $radioStreamerInternalEndpoint,
        string $radioStreamerToken,
        string $facebookAppId,
        string $facebookAppSecret,
        string $smtpHost,
        string $smtpUser,
        string $smtpPassword,
        int $smtpPort,
        string $webServerOwnAddress,
        string $fileServerOwnAddress
    ) {
        $this->radioStreamerEndpoint = $radioStreamerEndpoint;
        $this->radioStreamerInternalEndpoint = $radioStreamerInternalEndpoint;
        $this->radioStreamerToken = $radioStreamerToken;

        $this->facebookAppId = $facebookAppId;
        $this->facebookAppSecret = $facebookAppSecret;

        $this->smtpHost = $smtpHost;
        $this->smtpUser = $smtpUser;
        $this->smtpPassword = $smtpPassword;
        $this->smtpPort = $smtpPort;

        $this->webServerOwnAddress = $webServerOwnAddress;
        $this->fileServerOwnAddress = $fileServerOwnAddress;
    }

    /**
     * @throws ConfigException
     */
    public static function fromEnv(): Config
    {
        $radioStreamerEndpoint = env("RADIO_STREAMER_ENDPOINT");
        $radioStreamerInternalEndpoint = env("RADIO_STREAMER_INTERNAL_ENDPOINT");
        $radioStreamerToken = env("RADIO_STREAMER_TOKEN");

        if ($radioStreamerEndpoint === null || $radioStreamerInternalEndpoint === null || $radioStreamerToken === null) {
            throw new ConfigException(
                'Environment variables "RADIO_STREAMER_ENDPOINT", "RADIO_STREAMER_INTERNAL_ENDPOINT" and "RADIO_STREAMER_TOKEN" are required for operation'
            );
        }

        $facebookAppId = env('FACEBOOK_APP_ID');
        $facebookAppSecret = env('FACEBOOK_APP_SECRET');

        if ($facebookAppId === null || $facebookAppSecret === null) {
            throw new ConfigException(
                'Environment variables "FACEBOOK_APP_ID" and "FACEBOOK_APP_SECRET" are required for operation'
            );
        }


        $smtpHost = env('SMTP_HOST');
        $smtpUser = env('SMTP_USER');
        $smtpPassword = env('SMTP_PASSWORD');
        $smtpPort = intval(env('SMTP_PORT'), 10);

        if ($smtpHost === null || $smtpUser === null || $smtpPassword === null || $smtpPort === null) {
            throw new ConfigException(
                'Environment variables "SMTP_HOST", "SMTP_HOST", "SMTP_PASSWORD" and "SMTP_PORT" are required for operation'
            );
        }

        $webServerOwnAddress = env('WEB_SERVER_OWN_ADDRESS');

        if ($webServerOwnAddress === null) {
            throw new ConfigException(
                'Environment variable "WEB_SERVER_OWN_ADDRESS" is required for operation'
            );
        }

        $fileServerOwnAddress = env('FILE_SERVER_OWN_ADDRESS');

        if ($fileServerOwnAddress === null) {
            throw new ConfigException(
                'Environment variable "FILE_SERVER_OWN_ADDRESS" is required for operation'
            );
        }

        return new static(
            $radioStreamerEndpoint,
            $radioStreamerInternalEndpoint,
            $radioStreamerToken,
            $facebookAppId,
            $facebookAppSecret,
            $smtpHost,
            $smtpUser,
            $smtpPassword,
            $smtpPort,
            $webServerOwnAddress,
            $fileServerOwnAddress,
        );
    }

    public function getRadioStreamerEndpoint(): string
    {
        return $this->radioStreamerEndpoint;
    }

    public function getRadioStreamerInternalEndpoint(): string
    {
        return $this->radioStreamerInternalEndpoint;
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

    public function getSmtpHost(): string
    {
        return $this->smtpHost;
    }

    public function getSmtpUser(): string
    {
        return $this->smtpUser;
    }

    public function getSmtpPassword(): string
    {
        return $this->smtpPassword;
    }

    public function getSmtpPort(): int
    {
        return $this->smtpPort;
    }

    public function getEmailSenderEmail(): string
    {
        return $this->emailSenderEmail;
    }

    public function getEmailSenderName(): string
    {
        return $this->emailSenderName;
    }

    public function getWebServerOwnAddress(): string
    {
        return $this->webServerOwnAddress;
    }

    public function getFileServerOwnAddress(): string
    {
        return $this->fileServerOwnAddress;
    }
}
