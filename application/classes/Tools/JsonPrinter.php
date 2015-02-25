<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 22.12.14
 * Time: 15:52
 */

namespace Tools;


use Framework\Injector\Injectable;

class JsonPrinter implements SingletonInterface, Injectable {

    use Singleton;

    public function printJSON($data) {
        if (is_array($data) && $this->isIndexedArray($data)) {
            echo '[';
            $i = 0;
            foreach ($data as &$item) {
                if ($i++ > 0) {
                    echo ',';
                }
                $this->printJSON($item);
            }
            echo ']';
        } elseif (is_array($data)) {
            echo '{';
            $i = 0;
            foreach ($data as $key => &$item) {
                if ($i++ > 0) {
                    echo ',';
                }
                echo '"';
                echo $key;
                echo '":';
                $this->printJSON($item);
            }
            echo '}';
        } elseif (is_object($data) && $data instanceof \JsonSerializable) {
            $this->printJSON($data->jsonSerialize());
        } else {
            $this->escapeJsonString($data);
        }
    }

    /**
     * @param $value
     */
    function escapeJsonString($value) {
        if (is_numeric($value)) {
            echo intval($value);
        } elseif (is_bool($value)) {
            echo $value ? "true" : "false";
        } elseif (is_null($value)) {
            echo 'null';
        } else {
            echo '"';
            $escape = array("\\", "/", "\"", "\n", "\r", "\t", "\x08", "\x0c");
            $replacements = array("\\\\", "\\/", "\\\"", "\\n", "\\r", "\\t", "\\f", "\\b");
            $data = str_replace($escape, $replacements, strval($value));
            $data = preg_replace_callback("/[\x01-\x1f]/", function ($match) {
                return sprintf("\\u%04d", ord($match[0]));
            }, $data);
            echo $data;
            echo '"';
        }
    }

    private function isIndexedArray(array $array) {
        $iterator = 0;
        foreach ($array as $key => $value) {
            if ($key !== $iterator++) {
                return false;
            }
        }
        return true;
    }

    public function brOpenArray() {
        echo '[';
        return $this;
    }

    public function brCloseArray() {
        echo ']';
        return $this;
    }

    public function brOpenObject() {
        echo '{';
        return $this;
    }

    public function brCloseObject() {
        echo '}';
        return $this;
    }

    public function brComma() {
        echo ',';
        return $this;
    }

    public function brPrintKeyValue($key, $value) {
        $this->brPrintKey($key);
        $this->escapeJsonString($value);
        return $this;
    }

    public function brPrintKey($key) {
        echo '"';
        echo $key;
        echo '":';
        return $this;
    }

    public function brContentType() {
        header("Content-Type: application/json");
        return $this;
    }

    public function startGZ() {
        ob_start("ob_gzhandler");
        return $this;
    }

    public function successPrefix() {
        $this->startGZ();
        $this->brContentType();
        $this->brOpenObject();
        $this->brPrintKeyValue("code", 1);
        $this->brComma();
        $this->brPrintKeyValue("message", "OK");
        $this->brComma();

        return $this;
    }

} 