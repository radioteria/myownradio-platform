<?php

class File {
    private $file, $filepath;

    public function __construct($file) {
        $this->file = $file;
        $this->filepath = pathinfo($file);
    }
    
    private function testFound() {
        if (! file_exists($this->file))
        {
            throw new patFileNotFoundException($this->file);
        }
        return $this;
    }
    
    public function exists() {
        return file_exists($this->file);
    }
    
    public function delete() {
        $this->testFound();
        if (unlink($this->file) === false)
        {
            throw new patFileNotDeletedException($this->file);
        }
    }
    
    public function mtime() {
        $this->testFound();
        return filemtime($this->file);
    }

    public function size() {
        $this->testFound();
        return filesize($this->file);
    }

    public function dirname() {
        return $this->filepath['dirname'];
    }
    
    public function basename() {
        return $this->filepath['basename'];
    }
    
    public function extension() {
        return @$this->filepath['extension'];
    }
    
    public function filename() {
        return @$this->filepath['filename'];
    }
    
    public function path() {
        return $this->file;
    }
    
    public function __toString() {
        return $this->file;
    }
    
    public function isDir() {
        $this->testFound();
        return is_dir($this->file);
    }
    
    public function isReadable() {
        $this->testFound();
        return is_readable($this->file);
    }
    
    public function isRegular() {
        $this->testFound();
        return is_file($this->file);
    }
    
    public function isExecutable() {
        $this->testFound();
        return is_executable($this->file);
    }
    
    public function isWritable() {
        $this->testFound();
        return is_writable($this->file);
    }
    
    public function atime() {
        $this->testFound();
        return fileatime($this->file);
    }
    
    public function isLink() {
        $this->testFound();
        return is_link($this->file);
    }
    
    public function getDirContents() {

        if (!$this->isDir()) {
            throw new Exception(sprintf("Can't retrieve directory contents. '{$this->file}' is not a directory."), null, null);
        }

        if (!$this->isExecutable()) {
            throw new Exception("Can't retrieve directory contents. No permission.", null, null);
        }

        $container = array();
        
        $fs = opendir($this->file);
        while($file = readdir($fs)) {
            if($file === "." || $file === "..") {
                continue;
            }
            $container[] = new File($this->file . "/" . $file);
        }
        closedir($fs);
        
        return $container;
    }
    
    public function createNewFile() {
        $fd = fopen($this->file, "w"); 
        if ($fd === false) {
            throw new patFileNotWritableException($this->file);
        }
        fclose($fd);
        return $this;
    }
    
    public function createNewDirectory($recursive = false) {
        if ($this->exists()) {
            throw new Exception(sprintf("File '%s' exists", $this->file), 804, null);
        }
        
        $result = mkdir($this->file, 0777, $recursive);
        if ($result === false) {
            throw new patFileNotWritableException($this->file);
        }
    }

    public function getParent() {
        return new File($this->filepath['dirname']);
    }

    public function getContentType() {
        return mime_content_type($this->file);
    }

    public function getContents() {
        return file_get_contents($this->file);
    }

    public function echoContents() {
        $is = fopen($this->file, "r");
        while ($data = fread($is, 2048)) {
            echo $data;
            flush();
        }
        fclose($is);
    }

}
