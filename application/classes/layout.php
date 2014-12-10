<?php

class layout
{
    static function parseHashTags($inputString, $tag = "span")
    {
        return preg_replace_callback("/(\#[\w]+)/", function($match) use($tag) {
            $url_encoded = urlencode($match[0]);
            $html_special = htmlspecialchars($match[0]);
            return "<{$tag} class='hashtag-element'><a href='/search?q={$url_encoded}'>{$html_special}</a></{$tag}>";
        }, $inputString);
    }
}
