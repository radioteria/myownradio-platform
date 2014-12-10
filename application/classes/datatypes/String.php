<?php

class String
{
    private $data;
    public $length;
    
    public function __construct($string)
    {
        $this->data = $string;
        $this->length = strlen($string);
    }
    
    public function __toString()
    {
        return $this->data;
    }
    
    public function toUpperCase()
    {
        return new String(strtoupper($this->data));
    }
    
    public function toLowerCase()
    {
        return new String(strtolower($this->data));
    }

    public function equals($that) {
        if ($that instanceof String) {
            return $this->data === $that->data;
        } else {
            return $this->data === $that;
        }
    }

}
