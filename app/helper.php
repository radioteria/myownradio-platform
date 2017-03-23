<?php

function locateCommand($command) {
    return \app\Helpers\Shell::locate($command);
}

function locateCommandAndExecute($command, array $args)
{
    return \app\Helpers\Shell::locateAndExecute($command, $args);
}