<?php

class FileOutputStream
{
    private $fd = null, 
            $me = null,
            $append = null;
    public function __construct(File $file, $append = false)
    {
        $this->me = $file;
        $this->append = $append;
        if ($file->exists() === false)
        {
            throw new patFileNotFoundException($file->path());
        }
        
        if ( $file->isWritable() === false)
        {
            throw new patFileNotWritableException();
        }
        
        $this->fd = fopen($file->path(), $append ? "a" : "w");
        
        if ($this->fd === false)
        {
            throw new patFileNotWritableException();
        }
        
    }
    
    public function writeLong($value)
    {
        if (fwrite($this->fd, pack("V", (int)$value)) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function writeChar($value)
    {
        if (fwrite($this->fd, pack("C", (int)$value)) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function writeShort($value)
    {
        if (fwrite($this->fd, pack("S", (int)$value)) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function writeInteger($value)
    {
        if (fwrite($this->fd, pack("I", (int)$value)) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function writeDouble($value)
    {
        if (fwrite($this->fd, pack("d", (int)$value)) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function write($data)
    {
        if (fwrite($this->fd, $data) === false)
        {
            throw new patFileNotWritableException();
        }
        return $this;
    }

    public function close()
    {
        fclose($this->fd);
        return this;
    }
}
