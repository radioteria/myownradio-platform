<?php

class fileread
{
    private $fh = NULL,
            $fp = NULL,
            $fs = NULL;
    
    function __construct($fn)
    {
        $this->fh = fopen($fn, "r");
        if($this->exists())
        {
            $this->sizeCalc();
        }
    }
    
    function exists() 
    {
        return $this->fh !== false;
    }
    
    function read($length)
    {
            return fread($this->fh, $length);
    }
    
    function size()
    {
            return $this->fs;
    }
    
    private function sizeCalc()
    {
            $current = ftell($this->fh);
            fseek($this->fh, 0, SEEK_END);
            $this->fs = ftell($this->fh);
            fseek($this->fh, $current, SEEK_SET);
            return $this;
    }
    
    function savePos()
    {
            $this->fp = ftell($this->fh);
            return $this;
    }
    
    function getPos() {
        return $this->fp;
    }
    
    function goPos()
    {
        if(!is_null($this->fp))
        {
            fseek($this->fh, $this->fp, SEEK_SET);
        }
        return $this;
    }
    
    function seek($pos)
    {
        fseek($this->fh, $pos, SEEK_SET);
    }

    function seekForth($pos)
    {
        fseek($this->fh, $pos, SEEK_CUR);
    }
    
    function __destruct()
    {
        fclose($this->fh);
    }
    
    function feof()
    {
        return feof($this->fh);
    }

}