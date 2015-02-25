<?php

namespace Framework;

use stdClass;
use Tools\File;

class Template {

    private $template;
    private $variables;
    private $raw;

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
        $this->variables->{$key} = $value;
        $this->raw->{$key} = $raw;
        return $this;
    }

    /**
     * @return mixed
     */
    public function makeDocument() {
        $result = preg_replace_callback($this->buildReplace(), function ($match) {
            if (isset($this->variables->{$match[1]})) {
                if ($this->raw->{$match[1]} === false) {
                    return htmlspecialchars($this->variables->{$match[1]});
                } else {
                    return $this->variables->{$match[1]};
                }
            } else {
                return "";
            }
        }, $this->template);

        return $result;
    }

    public function reset() {
        $this->variables = new stdClass();
        $this->raw = new stdClass();
        return $this;
    }

    public function putObject($stream) {
        foreach ($stream as $key=>$val) {
            $this->addVariable($key, $val, false);
        }
    }
}
