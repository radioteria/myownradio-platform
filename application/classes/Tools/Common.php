<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 22:24
 */

namespace Tools;


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


} 