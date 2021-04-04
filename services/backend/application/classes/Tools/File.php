<?php

namespace Tools;

class File {
    private $file, $filepath;

    public function __construct($file) {
        $this->file = $file;
        $this->filepath = pathinfo($file);
    }

    private function testFound() {
        if (!file_exists($this->file)) {
            throw FileException::fileNotFound($this->file);
        }
        return $this;
    }

    public function exists() {
        return file_exists($this->file);
    }

    public function delete() {
        $this->testFound();
        if (unlink($this->file) === false) {
            throw FileException::noAccess($this->file);
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
            throw FileException::isNotDir($this->file);
        }

        if (!$this->isExecutable()) {
            throw FileException::noAccess($this->file);
        }

        $container = array();

        $fs = opendir($this->file);
        while ($file = readdir($fs)) {
            if ($file === "." || $file === "..") {
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
            throw FileException::noAccess($this->file);
        }
        fclose($fd);
        return $this;
    }

    public function createNewDirectory($rights = 0777, $recursive = false) {
        if ($this->exists()) {
            throw FileException::fileExists($this->file);
        }

        $result = mkdir($this->file, NEW_DIR_RIGHTS, $recursive);
        if ($result === false) {
            throw FileException::noAccess($this->file);
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

    public function show() {
        $this->testFound();
        $is = fopen($this->file, "r");
        while ($data = fread($is, 2048)) {
            echo $data;
            flush();
        }
        fclose($is);
    }

}
