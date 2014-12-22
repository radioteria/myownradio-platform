<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 15:52
 */

namespace Tools;


use Framework\Services\Injectable;

class JsonPrinter implements SingletonInterface, Injectable {

    use Singleton;

    public function printJSON($data) {
        if (is_string($data)) {
            $this->escapeJsonString($data);
        } elseif (is_numeric($data)) {
            echo intval($data);
        } elseif (is_null($data)) {
            echo 'null';
        } elseif (is_array($data) && $this->isIndexedArray($data)) {
            echo '[';
            $i = 0;
            foreach($data as &$item) {
                if ($i++ > 0) {
                    echo ',';
                }
                $this->printJSON($item);
            }
            echo ']';
        } elseif (is_array($data)) {
            echo '{';
            $i = 0;
            foreach($data as $key=>&$item) {
                if ($i++ > 0) {
                    echo ',';
                }
                echo '"';
                echo $key;
                echo '"';
                echo ':';
                $this->printJSON($item);
            }
            echo '}';
        } elseif(is_object($data) && $data instanceof \JsonSerializable) {
            $this->printJSON($data->jsonSerialize());
        } else {
            $this->escapeJsonString(strval($data));
        }
    }

    /**
     * @param $value
     */
    function escapeJsonString($value) {
        echo '"';
        $escapers       = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
        $replacements   = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
        echo str_replace($escapers, $replacements, $value);
        echo '"';
    }

    private function isIndexedArray(array $array) {
        $iterator = 0;
        foreach ($array as $key=>$value) {
            if ($key !== $iterator++) {
                return false;
            }
        }
        return true;
    }

} 