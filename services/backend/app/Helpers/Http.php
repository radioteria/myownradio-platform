<?php


namespace app\Helpers;


class Http
{
    public static function get(string $url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_exec($ch);

        curl_close($ch);
    }
}
