<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 19.12.14
 * Time: 14:17
 */

namespace Tools;


class String implements \Countable {

    private $string;

    function __construct($string = "") {
        $this->string = strval($string);
    }

    function append($string) {
        return new self($this->string . strval($string));
    }

    function toUpperCase() {
        return new self(mb_strtoupper($this->string, "UTF8"));
    }

    function toLowerCase() {
        return new self(mb_strtoupper($this->string, "UTF8"));
    }

    function upperCaseFirst() {
        $value = mb_strtoupper(substr($this->string, 0, 1), "UTF8") . substr($this->string, 1);
        return new self($value);
    }

    function index($needle, $offset = 0) {
        return strpos($this->string, $needle, $offset);
    }

    function rindex($needle, $offset = 0) {
        return strrpos($this->string, $needle, $offset);
    }

    function __toString() {
        return $this->string;
    }

    public function count() {
        return strlen($this->string);
    }
}