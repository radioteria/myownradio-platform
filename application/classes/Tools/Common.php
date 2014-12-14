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


} 