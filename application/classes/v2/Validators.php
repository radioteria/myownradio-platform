<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 12.12.14
 * Time: 9:35
 */

class Validators {

    /**
     * @param array $metadata
     * @return array
     * @throws validException
     */
    public static function trackMetadataValidator(array $metadata) {

        $optional = new Optional($metadata, function ($variable) {

            $reqKeys = array("artist", "title", "album", "track_number", "genre", "date");

            foreach($reqKeys as $key) {
                if (array_key_exists($key, $variable) === false) {
                    return false;
                }
            }

            return true;

        });

        return $optional->getOrElseThrow(new validException("Wrong metadata"));

    }

} 