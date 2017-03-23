<?php

function locateCommand(string $command): string {
    return \app\Helpers\Shell::locate($command);
}

function locateCommandAndExecute(string $command, array $args): array
{
    return \app\Helpers\Shell::locateAndExecute($command, $args);
}