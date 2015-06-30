<?php

namespace Framework;

use Framework\Services\Locale\I18n;
use Tools\File;

class Template {

    private $template;
    private $variables = [];

    private $prefix = "\${";
    private $suffix = "}";

    private static $templatePath = ".";

    public static function setTemplatePath($path) {
        self::$templatePath = $path;
    }

    /**
     * @return string
     */
    private function buildReplace() {
        return "/" . preg_quote($this->prefix) . "\\s*(.+?)\\s*(?:\\|\\s*(.+?)\\s*)*" . preg_quote($this->suffix) . "/";
    }

    /**
     * @param string $defaultPrefix
     */
    public function setPrefix($defaultPrefix) {
        $this->prefix = $defaultPrefix;
    }

    /**
     * @param string $defaultSuffix
     */
    public function setSuffix($defaultSuffix) {
        $this->suffix = $defaultSuffix;
    }

    /**
     * @param $template
     */
    public function __construct($template) {
        $file = new File(self::$templatePath . "/" . $template);
        $this->reset()->template = $file->getContents();
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function addVariable($key, $value) {
        $this->variables[$key] = $value;
        return $this;
    }

    /**
     * @return mixed
     */
    public function render() {
        $result = preg_replace_callback($this->buildReplace(), function ($match) {
            array_shift($match);
            $src = $this->getObjectParameter(array_shift($match));
            while ($filter = array_shift($match)) {
                $split = preg_split("~(\\s*\\:\\s*)~", $filter);
                $f = array_shift($split);
                $src = $this->filters($f, $src, $split);
            }
            return $src;
        }, $this->template);

        return $result;
    }

    public function display() {
        echo $this->render();
    }

    private function getObjectProperty($object, $property) {
        if (is_object($object)) {

        }
    }

    private function under2camel($text) {
        return "get" . (implode("", array_map(function ($p) { return ucfirst($p); }, explode("_", $text))));
    }

    private function getObjectParameter($key) {
        $slices = explode(".", $key);
        $current = $this->variables;
        foreach ($slices as $slice) {
            if (!isset($current[$slice])) {
                return "";
            };

            $current = $current[$slice];

            if (is_array($current)) {
                continue;
            }
        }
        switch (true) {
            case is_string($current):
            case is_numeric($current):
                return $current;
            case is_array($current):
                return json_encode($current);
            case is_object($current):
                return serialize($current);
            default:
                return "";
        }
    }

    /**
     * @return $this
     */
    public function reset() {
        $this->variables = [];
        return $this;
    }

    /**
     * @param $stream
     */
    public function putObject($stream) {
        foreach ($stream as $key => $val) {
            $this->addVariable($key, $val, false);
        }
    }

    /**
     * @param $template
     * @return \Closure
     */
    public static function map($template) {
        return function ($value) use (&$template) {
            $t = new self($template);
            $t->putObject($value);
            return $t->render();
        };
    }

    /**
     * @param $filter
     * @param $data
     * @param $args
     * @throws \Exception
     * @return object
     */
    private static function filters($filter, $data, $args) {
        $encoding = "utf8";

        switch ($filter) {
            case "uppercase":
                return mb_strtoupper($data, $encoding);

            case "lowercase":
                return mb_strtolower($data, $encoding);

            case "upperfirst":
                return ucfirst($data);

            case "upperwords":
                return ucwords($data);

            case "html":
                return htmlspecialchars($data, ENT_QUOTES);

            case "url":
                return urlencode($data);

            case "date":
                return date($args[0], $data);

            case "tr":
                return I18n::tr($data);

            default:
                throw new \Exception("Unknown filter used in template");
        }
    }

}
