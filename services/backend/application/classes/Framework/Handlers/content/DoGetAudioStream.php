<?php


namespace Framework\Handlers\content;


use app\Services\RadioStreamerService;
use Framework\Controller;
use Framework\Services\HttpGet;

class DoGetAudioStream implements Controller
{
    public function doGet(HttpGet $httpGet, RadioStreamerService $radioStreamerService) {
        $channelId = $httpGet->getRequired('s');
        $format = $httpGet->getRequired('f');
        $clientId = $httpGet->getParameter('client_id');

        $url = $radioStreamerService->getRadioChannelStreamUrl($channelId, $format, $clientId);

        header("Location: ${$url}");
    }
}
