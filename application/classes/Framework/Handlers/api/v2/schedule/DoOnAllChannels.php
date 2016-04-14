<?php
/**
 * @author Roman Gemini <roman_gemini@ukr.net>
 * @date 14.04.2016
 * @time 12:20
 */

namespace Framework\Handlers\api\v2\schedule;


use API\REST\TrackCollection;
use Framework\Controller;

class DoOnAllChannels implements Controller
{
    public function doGet(TrackCollection $trackCollection) {
        return $trackCollection->getPlayingOnAllChannels();
    }
}