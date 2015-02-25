<?php

namespace Framework;

use Tools\File;

class Template {

    private $template;
    private $variables = [];
    private $raw = [];

    private $prefix = "\${";
    private $suffix = "}";

    /**
     * @return string
     */
    private function buildReplace() {
        return "/" . preg_quote($this->prefix) . "\\s*(.+?)\\s*" . preg_quote($this->suffix) . "/";
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
        $file = new File($template);
        $this->reset()->template = $file->getContents();
    }

    /**
     * @param $key
     * @param $value
     * @param bool $raw
     * @return $this
     */
    public function addVariable($key, $value, $raw = false) {
        $this->variables[$key] = $value;
        $this->raw[$key] = $raw;
        return $this;
    }

    /**
     * @return mixed
     */
    public function makeDocument() {
        $result = preg_replace_callback($this->buildReplace(), function ($match) {
            return $this->getObjectParameter($match[1]);
        }, $this->template);

        return $result;
    }

    private function getObjectParameter($key) {
        $slices = explode(".", $key);
        $current = $this->variables;
        foreach ($slices as $slice) {
            if (!isset($current[$slice])) { return ""; };

            $current = $current[$slice];

            if (is_array($current)) {
                continue;
            }
        }
        return is_array($current) ? "[array]" : $current;
    }

    /**
     * @return $this
     */
    public function reset() {
        $this->variables = [];
        $this->raw = [];
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
}
