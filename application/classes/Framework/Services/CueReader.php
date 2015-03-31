<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 17.02.15
 * Time: 16:21
 */

namespace Framework\Services;


class CueReader {
    private $cueData = null;
    private $cueTitle = null;
    private $cuePerformer = null;
    private $tracks = [];

    function __construct($cueData) {
        $this->cueData = $cueData;
        $this->parseCue();
    }

    private function parseCue() {

        $index = -1;
        $lines = explode("\n", $this->cueData);

        foreach ($lines as $line) {

            if (preg_match("/TITLE\\s\"(.+)\"/", $line, $match)) {

                if ($index == -1) {
                    $this->cueTitle = $match[1];
                } else {
                    $this->tracks[$index]["title"] = $match[1];
                }

            } else if (preg_match("/PERFORMER\\s\"(.+)\"/", $line, $match)) {

                if ($index == -1) {
                    $this->cuePerformer = $match[1];
                } else {
                    $this->tracks[$index]["artist"] = $match[1];
                }

            } else if (preg_match("/TRACK\\s(\\d+)\\sAUDIO/", $line, $match)) {

                $this->tracks[++ $index] = [];

            } else if (preg_match("/INDEX\\s+\\d+\\s+(.+)/", $line, $match)) {

                $milliseconds = $this->timeToMilliseconds($match[1]);

                if (empty($this->tracks[$index]["offset"])) {
                    $this->tracks[$index]["offset"] = $milliseconds;
                }

            }

        }

    }

    private function timeToMilliseconds($timeString) {
        $result = 0;
        $parts = explode(":", $timeString);
        $result += $parts[0] * 60000;
        $result += $parts[1] * 1000;
        $result += $parts[2] * 10;
        return $result;
    }

} 