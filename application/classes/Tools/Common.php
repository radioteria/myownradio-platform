<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:24
 */

namespace Tools;


use Framework\Exceptions\ApplicationException;
use Framework\Exceptions\ControllerException;
use Framework\Services\Config;
use Framework\Services\HttpRequest;

class Common {

    const GENERATED_ID_LENGTH = 8;

    public static function searchQueryFilter($text) {

        $query = "";
        $stop = "\\+\\-\\>\\<\\(\\)\\~\\*\\\"\\@";
        $words = preg_split("/(*UTF8)(?![\\p{L}|\\p{N}|\\#]+)|([$stop]+)/", $text);

        foreach ($words as $word) {
            if (strlen($word) > 0) {
                $query .= "+{$word}";
            }
        }

        if (strlen($query))
            $query .= "*";

        return $query;

    }

    static function generateUniqueID() {

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
        if ($httpCode != 404 && $httpCode != 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param $str
     * @return mixed|string
     */
    static function toAscii($str) {
        $clean = preg_replace("/[^a-zA-Z0-9\\/_|+ -]/", '', $str);
        $clean = strtolower(trim($clean, '-'));
        $clean = preg_replace("/[\\/_|+ -]+/", '-', $clean);
        return $clean;
    }

    /**
     * @param $text
     * @return mixed
     */
    static function toTransliteration($text) {
        $rus = array('А', 'Б', 'В', 'Г', 'Д', 'Е', 'Ё', 'Ж', 'З', 'И', 'Й', 'К', 'Л', 'М', 'Н', 'О', 'П', 'Р', 'С', 'Т', 'У', 'Ф', 'Х', 'Ц', 'Ч', 'Ш', 'Щ', 'Ъ', 'Ы', 'Ь', 'Э', 'Ю', 'Я', 'а', 'б', 'в', 'г', 'д', 'е', 'ё', 'ж', 'з', 'и', 'й', 'к', 'л', 'м', 'н', 'о', 'п', 'р', 'с', 'т', 'у', 'ф', 'х', 'ц', 'ч', 'ш', 'щ', 'ъ', 'ы', 'ь', 'э', 'ю', 'я');
        $lat = array('A', 'B', 'V', 'G', 'D', 'E', 'E', 'Gh', 'Z', 'I', 'Y', 'K', 'L', 'M', 'N', 'O', 'P', 'R', 'S', 'T', 'U', 'F', 'H', 'C', 'Ch', 'Sh', 'Sch', 'Y', 'Y', 'Y', 'E', 'Yu', 'Ya', 'a', 'b', 'v', 'g', 'd', 'e', 'e', 'gh', 'z', 'i', 'y', 'k', 'l', 'm', 'n', 'o', 'p', 'r', 's', 't', 'u', 'f', 'h', 'c', 'ch', 'sh', 'sch', 'y', 'y', 'y', 'e', 'yu', 'ya');
        return str_replace($rus, $lat, $text);
    }

    /**
     * @param $filename
     * @return Optional[]
     * @throws \Framework\Exceptions\ControllerException
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
            if (array_search($language, array('uk', 'ru')) !== false) {
            }
        });

        foreach ($tagsData as &$tag) {
            $tag = self::cp1252dec($tag);
        }

        $tagsData[0] = Optional::ofZeroable($tagsData[0]);

        for ($i = 1; $i < count($tagsData); $i++) {
            $tagsData[$i] = Optional::ofEmpty($tagsData[$i]);
        }

        $tagsArray = array_combine($tagsList, $tagsData);

        return $tagsArray;

    }

    static function cp1252dec($chars) {

        $test = @iconv("UTF-8", "CP1252", $chars);

        if ($test) {
            return iconv("CP1251", "UTF-8", $test);
        } else {
            return $chars;
        }

    }


} 