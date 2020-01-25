<?php


namespace Framework\Handlers\api;


use Framework\Controller;
use Framework\Services\JsonResponse;
use Framework\Services\RouteParams;
use REST\Playlist;
use REST\Streams;

/**
 * Created by PhpStorm.
 * User: roman
 * Date: 1/25/00
 * Time: 11:18 PM
 */
class DoChannelInfo implements Controller
{
    public function doGet(RouteParams $params, Playlist $playlist, Streams $streams, JsonResponse $response)
    {
        $stream_id = $params->getRequired("stream_id");
        $stream = $streams->getOneStream($stream_id);

        $response->setData([
            "name" => $stream["name"],
            "status" => $stream["status"],
        ]);
    }
}
