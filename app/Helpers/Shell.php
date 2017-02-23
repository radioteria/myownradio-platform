<?php

namespace app\Helpers;

class Shell
{
    public static function locate($command): string
    {
        exec("which " . escapeshellcmd($command), $response, $status);

        if ($status == 0 && sizeof($response) > 0) {
            return trim($response[0]);
        }

        throw new \InvalidArgumentException("Command '$command' does not exist.");
    }

    public static function locateAndExecute($command, array $args = []): array
    {
        $fullPath = self::locate($command);

        $arguments = implode(' ', array_map('escapeshellarg', $args));

        exec("$fullPath $arguments", $response, $status);

        if ($status != 0) {
            throw new \InvalidArgumentException("Command '$command' returned with non-zero exit status ($status).");
        }

        return $response;
    }
}
