<?php
/**
 * Created by PhpStorm.
 * User: roman
 * Date: 13.12.14
 * Time: 22:24
 */

namespace Tools;


class Common {

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

} 