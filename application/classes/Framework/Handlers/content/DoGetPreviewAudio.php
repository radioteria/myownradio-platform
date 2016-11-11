<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 16.02.15
 * Time: 10:06
 */

namespace Framework\Handlers\content;

use app\Services\Storage\StorageFactory;
use Framework\Controller;
use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\FileServer\FSFile;
use Framework\Models\AuthUserModel;
use Framework\Services\HttpGet;
use Framework\View\Errors\View401Exception;
use Framework\View\Errors\View404Exception;
use Objects\Track;

class DoGetPreviewAudio implements Controller
{
    public function doGet(HttpGet $get, AuthUserModel $user)
    {
        try {
            $id = $get->getRequired("id");

            /**
             * @var Track $track
             */
            $track = Track::getByID($id)->getOrElseThrow(new View404Exception());


            if ($track->getUserID() != $user->getID()) {
                throw new View401Exception();
            }

            if ($track->getIsNew() != 0) {
                $track->setIsNew(0);
                $track->save();
            }

            if ($track->getFileId() === null) {
                throw new View404Exception();
            }

            header("Content-Type: audio/mp3");
            set_time_limit(0);

            $program = config('services.ffmpeg_cmd') . ' ' . config('services.ffmpeg.preview_args');

            $hash = $track->getHash();
            $storage = StorageFactory::getStorage();

            $url = $storage->url(FSFile::getPathByHash($hash));

            $urlHttp = str_replace('https', 'http', $url);

            $command = sprintf($program, $track->getDuration() / 3000, escapeshellarg($urlHttp));

            $proc = popen($command, "r");

            if (!$proc) {
                throw new ApplicationException("Could not start - {$command}");
            }

            while ($data = fread($proc, 4096)) {
                echo $data;
                flush();
            }

            $status = pclose($proc);

            if ($status != 0) {
                throw new ApplicationException("Status({$status}) {$command}");
            }

        } catch (ControllerException $exception) {
            echo $exception->getMyMessage();
            http_response_code(404);
        }
    }
} 