<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 22:24
 */

namespace Tools;


use MVC\Exceptions\ApplicationException;
use MVC\Exceptions\ControllerException;
use MVC\Services\Config;
use MVC\Services\HttpRequest;

class Common {

    const GENERATED_ID_LENGTH = 8;

    public static function searchQueryFilter($text) {

        $query = "";
        $words = preg_split("/(*UTF8)((?![\\p{L}|\\p{N}|\\#])|(\\s))+/", $text);

        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $query .= "+{$word} ";
            }
        }

        $query .= "*";

        return $query;

    }

    static function generateUniqueId() {

        $idCharacters = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890";
        $id = "";

        for ($i = 0; $i < self::GENERATED_ID_LENGTH; $i++) {
            $id .= substr($idCharacters, rand(0, strlen($idCharacters) - 1), 1);
        }

        return $id;

    }

    static function pageExists($url) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if($httpCode != 404 && $httpCode != 0) {
            return true;
        } else {
            return false;
        }
    }

    static function toAscii($str) {
        $clean = preg_replace("/[^a-zA-Z0-9\\/_|+ -]/", '', $str);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\\/_|+ -]+/", '-', $clean);
        return $clean;
    }

    /**
     * @param $filename
     * @return Optional[]
     * @throws \MVC\Exceptions\ControllerException
     */
    static function getAudioTags($filename) {

        $fetcher = Config::getInstance()->getSetting("getters", "mediainfo")
            ->getOrElseThrow(ApplicationException::of("NO MEDIA INFO GETTER"));

        $request = HttpRequest::getInstance();

        setlocale(LC_ALL, "en_US.UTF-8");

        $fnQuote = escapeshellarg($filename);

        $fetchCommand = $fetcher . "  --Inform=\"General;%Duration%\\n%Title%\\n%Track/Position%\\n%Album%\\n%Performer%\\n%Genre%\\n%Album/Performer%\\n%Recorded_Date%\" " . $fnQuote;

        exec($fetchCommand, $tagsData, $exit);

        $tagsList = array('DURATION', 'TITLE', 'TRACKNUMBER', 'ALBUM', 'PERFORMER', 'GENRE', 'ALBUM_PERFORMER', 'RECORDED_DATE');

        if (count($tagsData) != count($tagsList)) {
            throw new ControllerException("Uploaded file has incorrect tags");
        }

        $request->getLanguage()->then(function ($language) use ($tagsData) {
            if(array_search($language, array('uk', 'ru')) !== false) {
                foreach($tagsData as &$tag) {
                    $tag = self::cp1252dec($tag);
                }
            }
        });

        foreach($tagsData as &$tag) {
            $tag = Optional::ofEmpty($tag);
        }

        $tagsArray = array_combine($tagsList, $tagsData);

        return $tagsArray;

    }

    static function cp1252dec($chars) {

        $test = @iconv("UTF-8", "CP1252", $chars);

        if($test) {
            return iconv("CP1251", "UTF-8", $test);
        } else {
            return $chars;
        }

    }


} 