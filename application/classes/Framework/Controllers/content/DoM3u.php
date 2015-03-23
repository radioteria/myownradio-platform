<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 03.03.15
 * Time: 16:48
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Services\HttpGet;
use Framework\Template;
use Framework\View\Errors\View404Exception;
use Objects\Stream;

class DoM3u implements Controller {
    public function doGet(HttpGet $get) {

        $id = $get->getRequired("stream_id");
        $template = new Template("application/tmpl/playlist.tmpl");

        /** @var Stream $stream */
        $stream = Stream::getByFilter("GET_BY_KEY", [":key" => $id])->getOrElseThrow(new View404Exception());

        $template->addVariable("stream_name", $stream->getName());
        $template->addVariable("stream_id", $stream->getID());

        header("Content-Type: audio/mpegurl");
        header("Content-Disposition: attachment; filename=" . $stream->getName() . " on MYOWNRADIO.BIZ.m3u");

        echo $template->makeDocument();

    }
} 