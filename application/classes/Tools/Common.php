<?php
/**
 * Created by PhpStorm.
 * UserModel: roman
 * Date: 13.12.14
 * Time: 22:24
 */

namespace Tools;


class Common {

    const GENERATED_ID_LENGTH = 8;

    /**
     * @param $delimiter
     * @param string|null $str
     * @return array|null
     */
    public static function split($delimiter, $str) {
        if ($str === null) return null;
        return explode($delimiter, $str);
    }

    /**
     * @param string $pattern
     * @param array $args
     * @return mixed
     */
    public static function quickReplace($pattern, array $args = null) {
        return preg_replace_callback('~%([a-z0-9\_\.]+)%~', function ($match) use ($args) {
            $key = $match[1];
            if (isset($args[$key])) {
                return $args[$key];
            } else {
                return "";
            }
        }, $pattern);
    }

    /**
     * @param string $template
     * @param array $context
     * @return string
     */
    public static function deepTemplate($template, array $context = null) {
        return preg_replace_callback('~{{\s*([a-z0-9\_\.]+)\s*}}~', function ($match) use ($context) {
            $path = explode(".", $match[1]);
            $current = $context;
            foreach ($path as $item) {
                if (is_array($current) && array_key_exists($item, $current)) {
                    $current = $current[$item];
                } else {
                    return "";
                }
            }
            return $current;
        }, $template);
    }

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

    static function generateUniqueId($length = self::GENERATED_ID_LENGTH) {

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

    static function generateColor($maxLevel) {
        $data = [];
        $max = 0;
        for ($times = 3; $times --;) {
            $value = rand(0, $maxLevel);
            $data[] = $value;
            if ($value > $max) {
                $max = $value;
            }
        }

        $shade = $max / $maxLevel;

        $color = "";
        foreach ($data as $value) {
            $color .= dechex($value * $shade);
        }
        return $color;
    }

    static function createTemporaryImage($path = null, $w = 500, $h = 500) {

        $colors = ["28242D", "4C3746", "353952", "454853", "375044", "57564B", "433B46", "23441F", "5B1F3A"];

        $width = 64;
        $height = 64;

        $a_color = $colors[rand(0, count($colors) - 1)];
        $b_color = $colors[rand(0, count($colors) - 1)];
        $c_color = $colors[rand(0, count($colors) - 1)];
        $d_color = $colors[rand(0, count($colors) - 1)];

        $image = imagecreatetruecolor($width, $height);

        for ($y = 0; $y < $height; $y ++) {
            for ($x = 0; $x < $width; $x ++) {
                $a_r_level = hexdec(substr($a_color, 0, 2)) / $width * ($width - $x) / $height * ($height - $y);
                $a_g_level = hexdec(substr($a_color, 2, 2)) / $width * ($width - $x) / $height * ($height - $y);
                $a_b_level = hexdec(substr($a_color, 4, 2)) / $width * ($width - $x) / $height * ($height - $y);

                $b_r_level = hexdec(substr($b_color, 0, 2)) / $width * $x / $height * ($height - $y);
                $b_g_level = hexdec(substr($b_color, 2, 2)) / $width * $x / $height * ($height - $y);
                $b_b_level = hexdec(substr($b_color, 4, 2)) / $width * $x / $height * ($height - $y);

                $c_r_level = hexdec(substr($c_color, 0, 2)) / $width * ($width - $x) / $height * $y;
                $c_g_level = hexdec(substr($c_color, 2, 2)) / $width * ($width - $x) / $height * $y;
                $c_b_level = hexdec(substr($c_color, 4, 2)) / $width * ($width - $x) / $height * $y;

                $d_r_level = hexdec(substr($d_color, 0, 2)) / $width * $x / $height * $y;
                $d_g_level = hexdec(substr($d_color, 2, 2)) / $width * $x / $height * $y;
                $d_b_level = hexdec(substr($d_color, 4, 2)) / $width * $x / $height * $y;


                $color = imagecolorresolve(
                    $image,
                    $a_r_level + $b_r_level + $c_r_level + $d_r_level,
                    $a_g_level + $b_g_level + $c_g_level + $d_g_level,
                    $a_b_level + $b_b_level + $c_b_level + $d_b_level
                );
                imagesetpixel($image, $x, $y, $color);
            }
        }

        $destination = imagecreatetruecolor($w, $h);

        imagecopyresampled($destination, $image, 0, 0, 0, 0,
            imagesx($destination), imagesy($destination), $width, $height);


        $overlay = imagecreatefrompng("images/logos/cover-overlay.png");

        imagecopyresampled($destination, $overlay, 0, 0, 0, 0,
            imagesx($destination), imagesy($destination), imagesx($overlay), imagesy($overlay));

        error_log("Save image: " . $path);

        imagepng($destination, $path);
        imagedestroy($destination);

    }

    /**
     * @param $filename
     * @return Optional
     * @throws \Framework\Exceptions\ControllerException
     */
    static function getAudioDuration($filename) {

        $fetcher = "/usr/local/bin/mediainfo";

        $fnQuote = escapeshellarg($filename);

        $fetchCommand = $fetcher . "  --Inform=\"General;%Duration%\" " . $fnQuote;

        exec($fetchCommand, $tagsData, $exit);

        return Optional::ofNullable(isset($tagsData[0]) ? $tagsData[0] : null);

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