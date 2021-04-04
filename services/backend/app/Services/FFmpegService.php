<?php

namespace app\Services;

use Tools\Singleton;
use Tools\SingletonInterface;

class FFmpegService implements SingletonInterface
{
    use Singleton;

    /**
     * Converts text from CP1251 to UTF-8 encoding.
     *
     * @param $chars
     * @return string
     */
    static function cp1251dec($chars)
    {

        $test = @iconv("UTF-8", "CP1252", $chars);

        if (is_null($test)) {
            return $chars;
        } else {
            return iconv("CP1251", "UTF-8", $test);
        }

    }

    public function getCommonMetadata(string $filename): ?array
    {

        $escapedFilename = escapeshellarg($filename);

        $command = sprintf(
            "%s -i %s -v quiet -print_format json -show_format",
            "ffprobe",
            $escapedFilename
        );

        exec($command, $result, $status);

        if ($status !== 0) {
            // @todo logs
            return null;
        }


        $metadata = json_decode(implode("", $result), true);

        $format = $metadata["format"];
        $tags = $format["tags"];

        return [
            "duration" => $format["duration"] ? +$format["duration"] * 1000 : null,
            "bitrate" => intval($format["bit_rate"]),
            "artist" => $tags["artist"] ?? $tags["ARTIST"] ?? null,
            "title" => $tags["title"] ?? $tags["TITLE"] ?? $tags["name"] ?? null,
            "genre" => $tags["genre"] ?? $tags["GENRE"] ?? null,
            "date" => $tags["date"] ?? $tags["DATE"] ?? null,
            "album" => $tags["album"] ?? $tags["ALBUM"] ?? null,
            "track_number" => isset($tags["track"]) ? (function ($trackNumber) {
                if (preg_match('`(\d+)/\d+`', $trackNumber, $matches)) {
                    return +$matches[1];
                }
                return +$trackNumber;
            })($tags["track"]) : null,
            "disc_number" => isset($tags["disc"]) ? +$tags["disc"] : null,
            "album_artist" => $tags["album_artist"] ?? null,
            "is_compilation" => isset($tags["compilation"]),
            "comment" => $tags["comment"] ?? null,
        ];
    }
}
