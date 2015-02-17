<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.02.15
 * Time: 10:06
 */

namespace Framework\Controllers\content;


use Framework\Controller;
use Framework\Exceptions\ControllerException;
use Framework\Models\AuthUserModel;
use Framework\Services\Config;
use Framework\Services\HttpGet;

use Objects\Track;
use Tools\File;

class DoGetPreviewAudio implements Controller {
    public function doGet(HttpGet $get, AuthUserModel $user, Config $config) {
        try {
            $id = $get->getRequired("id");
            $track = Track::getByID($id)->getOrElseThrow(ControllerException::noTrack($id));
            /** @var Track $track */

            if ($track->getUserID() != $user->getID()) {
                throw ControllerException::noPermission();
            }

            header("Content-Type: audio/mp3");
            if (strtolower($track->getExtension()) == "mp3") {

                $file = new File($track->getOriginalFile());
                $file->echoContents();
            } else {
                $program = $config->getSetting("streaming", "track_preview")
                    ->getOrElseThrow(ControllerException::of("no preview configured"));

                $process = sprintf($program, escapeshellarg($track->getOriginalFile()));

                $proc = popen($process, "r");
                while ($data = fread($proc, 4096)) {
                    echo $data;
                    flush();
                }
                pclose($proc);
            }

        } catch (ControllerException $exception) {
            echo $exception->getMyMessage();
            http_response_code(404);
        }
    }
} 