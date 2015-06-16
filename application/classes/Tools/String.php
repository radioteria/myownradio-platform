<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:17
 */

namespace Tools;


use Framework\Object;

class String implements \Countable {

    use Object;

    private $string;

    function __construct($string = "") {
        $this->string = strval($string);
    }

    function concat($string) {
        return new self($this->string . strval($string));
    }

    function contains($needle, $offset = null) {
        return strpos($this->string, $needle, $offset) !== false;
    }

    function endsWith($string) {
        return strrpos($this->string, $string) === strlen($this->string) - 1;
    }

    function equals($string) {
        if ($string instanceof self) {
            return $string->string === $this->string;
        } else {
            return $string === $this->string;
        }
    }

    function equalsIgnoreCase($string) {
        if ($string instanceof self) {
            return mb_strtolower($string->string, "utf8") === mb_strtolower($this->string, "utf8");
        } else {
            return $string === $this->string;
        }
    }

    function toUpperCase() {
        return new self(mb_strtoupper($this->string, "utf8"));
    }

    function toLowerCase() {
        return new self(mb_strtoupper($this->string, "utf8"));
    }

    function upperCaseFirst() {
        $value = mb_strtoupper(substr($this->string, 0, 1), "utf8") . substr($this->string, 1);
        return new self($value);
    }

    function indexOf($needle, $offset = 0) {
        return strpos($this->string, $needle, $offset);
    }

    function lastIndexOf($needle, $offset = 0) {
        return strrpos($this->string, $needle, $offset);
    }

    function isEmpty() {
        return strlen($this->string) == 0;
    }

    function length() {
        return strlen($this->string);
    }

    function matches($regexp) {
        return preg_match($regexp, $this->string);
    }

    function replaceAll($target, $replacement, $count = null) {
        return new self(str_replace($target, $replacement, $this->string, $count));
    }

    function replaceFirst($target, $replacement) {
        return $this->replaceAll($target, $replacement, 1);
    }

    function startsWith($string) {
        return strpos($this->string, $string) === 0;
    }

    function split($delimiter) {
        $parts = explode($delimiter, $this->string);
        $result = [];
        foreach ($parts as $part) {
            $result[] = new self($part);
        }
        return $result;
    }

    function splitRegexp($regexp) {
        $parts = preg_split($regexp, $this->string);
        $result = [];
        foreach ($parts as $part) {
            $result[] = new self($part);
        }
        return $result;
    }

    function substring($start, $length = null) {
        return new self(substr($this->string, $start, $length));
    }

    function trim() {
        return new self(trim($this->string));
    }

    function intern() {
        return $this->string;
    }

    function __toString() {
        return $this->string;
    }

    public function count() {
        return strlen($this->string);
    }
}