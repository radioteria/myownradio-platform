<?php

use \Framework\ApplicationException;

function locateCommand($command) {
    return \app\Helpers\Shell::locate($command);
}

function locateCommandAndExecute($command, array $args)
{
    return \app\Helpers\Shell::locateAndExecute($command, $args);
}

function sendAsMp3Stream($url, $position)
{
    $program = config('services.ffmpeg_cmd') . ' ' . config('services.ffmpeg.preview_args');

    $urlHttp = str_replace('https', 'http', $url);
    $command = sprintf($program, $position, escapeshellarg($urlHttp));

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
}
