<?php

/**
 * Created by PhpStorm.
 * UserModel: Roman
 * Date: 03.12.14
 * Time: 10:02
 */
class Arrays
{
    public static function array_map($array, $callback)
    {

        foreach($array as &$el)
        {
            $el = $callback($el);
        }

        return $array;

    }
} 