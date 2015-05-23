<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 06.05.15
 * Time: 20:21
 */

namespace Tools;


use Framework\Preferences;

class FFProbe implements \JsonSerializable {

    private $filename = null;
    private $formatName = null;
    private $duration = null;
    private $size = null;
    private $bitrate = null;
    private $metaArtist = null;
    private $metaTitle = null;
    private $metaGenre = null;
    private $metaDate = null;
    private $metaAlbum = null;
    private $metaTrackNumber = null;


    /**
     * @param string $filename
     * @return Optional
     */
    public static function read($filename) {

        if (! file_exists($filename)) {
            error_log("File {$filename} doesn't exists!");
            return Optional::noValue();
        }

        $escapedFilename = escapeshellarg($filename);

        $ffprobe = Preferences::getSetting("tools", "ffprobe");
        $command = Preferences::getSetting("tools", "ffprobe.metadata.template", [
            "ffprobe" => $ffprobe,
            "file" => $escapedFilename
        ]);

        exec($command, $result, $status);

        if ($status != 0) {
            return Optional::noValue();
        }

        $json = json_decode(implode("", $result), true);

        $object = new self();

        $object->filename               = @$json["format"]["filename"];
        $object->formatName             = @$json["format"]["format_name"];
        $object->duration               = intval(@$json["format"]["duration"]);
        $object->size                   = intval(@$json["format"]["size"]);
        $object->bitrate                = intval(@$json["format"]["bit_rate"]);

        if (isset($json["format"]["tags"])) {
            $object->metaArtist         = @$json["format"]["tags"]["artist"];
            $object->metaTitle          = @$json["format"]["tags"]["title"];
            $object->metaGenre          = @$json["format"]["tags"]["genre"];
            $object->metaDate           = @$json["format"]["tags"]["date"];
            $object->metaAlbum          = @$json["format"]["tags"]["album"];
            $object->metaTrackNumber    = @$json["format"]["tags"]["track"];
        }

        return Optional::hasValue($object);

    }

    /**
     * @return mixed
     */
    public function getBitrate() {
        return $this->bitrate;
    }

    /**
     * @return mixed
     */
    public function getDuration() {
        return $this->duration;
    }

    /**
     * @return mixed
     */
    public function getDurationMilliseconds() {
        if ($this->getDuration() === null) {
            return null;
        }
        return (int) ($this->duration * 1000);
    }

    /**
     * @return mixed
     */
    public function getFileName() {
        return $this->filename;
    }

    /**
     * @return mixed
     */
    public function getFormatName() {
        return $this->formatName;
    }

    /**
     * @return mixed
     */
    public function getMetaAlbum() {
        return $this->metaAlbum;
    }

    /**
     * @return mixed
     */
    public function getMetaArtist() {
        return $this->metaArtist;
    }

    /**
     * @return mixed
     */
    public function getMetaDate() {
        return $this->metaDate;
    }

    /**
     * @return mixed
     */
    public function getMetaGenre() {
        return $this->metaGenre;
    }

    /**
     * @return mixed
     */
    public function getMetaTitle() {
        return $this->metaTitle;
    }

    /**
     * @return mixed
     */
    public function getMetaTrackNumber() {
        return $this->metaTrackNumber;
    }

    /**
     * @return mixed
     */
    public function getSize() {
        return $this->size;
    }


    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize() {
        $data = [];
        foreach((new \ReflectionClass($this))->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
            if (substr($method->getName(), 0, 3) == "get") {
                $underscore = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', substr($method->getName(), 3)));
                $data[$underscore] = $method->invoke($this);
            }
        }
        return $data;
    }
}