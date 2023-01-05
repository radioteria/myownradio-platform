<?php


namespace app\Services;


use app\Config\Config;
use app\Logger;
use Framework\Injector\Injectable;
use GuzzleHttp\Client;
use Tools\Optional;
use Tools\Singleton;
use Tools\SingletonInterface;

class RadioStreamerService implements Injectable, SingletonInterface
{
    use Singleton;

    private Config $config;
    private Client $client;
    private Logger $logger;

    public function __construct()
    {
        $this->config = Config::getInstance();
        $this->logger = Logger::getInstance();
        $this->client = new Client();
    }

    public function restartRadioChannel(int $channelId): void
    {
        $uri = sprintf("%s/v2/restart/%d", $this->config->getRadioStreamerInternalEndpoint(), $channelId);

        $response = $this->client->request('GET', $uri, [
            'headers' => [
                'token' => $this->config->getRadioStreamerToken()
            ]
        ]);

        if ($response->getStatusCode() !== 200) {
            $this->logger->warning("Unexpected status code on restart audio channel", [
                "status_code" => $response->getStatusCode(),
            ]);
        }
    }

    public function getRadioChannelStreamUrl(int $channelId, Optional $audioFormat, Optional $clientId): string
    {
        $f = $audioFormat->map(function ($format) {
            return "format=${format}";
        });
        $c = $clientId->map(function ($clientId) {
            return "client_id=${clientId}";
        });

        $queryParameters = implode("&", $f->zip($c)->getOrElse([]));

        return sprintf(
            "%s/v2/listen/%d?%s",
            $this->config->getRadioStreamerEndpoint(),
            $channelId,
            $queryParameters
        );
    }

    // @todo
    private function getEndpoints(): array
    {
        return array_map(function ($record) {
            return $record['ip'];
        }, dns_get_record($this->config->getRadioStreamerEndpoint(), DNS_A));
    }
}
