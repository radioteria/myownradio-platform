<?php
/**
 * Created by PhpStorm.
 * User: Roman
 * Date: 23.12.14
 * Time: 16:38
 */

namespace Framework\Services\Module;

use Objects\Module;

class ModuleObject {

    /** @var Module $module */

    private $module;

    function __construct($module) {
        $this->module = Module::getByFilter("name = :key OR alias = :key", [":key" => $module])
            ->getOrElseThrow(new ModuleNotFoundException());
    }

    function getHtml() {
        return $this->module->getHtml();
    }

    function getCSS() {
        return $this->module->getCss();
    }

    function getJS() {
        return $this->module->getJs();
    }

    function getPost() {
        return $this->module->getPost();
    }

    function getName() {
        return $this->module->getName();
    }

    function executePost() {
        return eval("?>" . $this->module->getPost());
    }


    function executeHtml() {

        $parsed = preg_replace_callback("/\\<\\!\\-\\-\\s+module\\:(.+)\\s+\\-\\-\\>/", function ($match) {
            try {
                $module = new self($match[1]);
                return $module->executeHtml();
            } catch (ModuleNotFoundException $e) {
                return '[Module not found]';
            }
        }, $this->module->getHtml());

        // CSS Style Sheet
        if(strlen($this->module->getCss()) > 0) {
            if(strpos($this->module->getHtml(), '<!-- include:css -->', 0) !== false) {
                $parsed = str_replace('<!-- include:css -->', "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$this->getName()}.css\" />\r\n", $parsed);
            } else {
                $parsed .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$this->getName()}.css\" />\r\n";
            }
        }

        // Templates
        if(strlen($this->module->getTmpl()) > 0) {
            if(strpos($this->module->getHtml(), '<!-- include:tmpl -->', 0) !== false)
            {
                $parsed = str_replace('<!-- include:tmpl -->', $this->module->getTmpl(), $parsed);
            } else {
                $parsed .= $this->module->getTmpl();
            }
        }

        // JavaScript
        if(strlen($this->module->getJs()) > 0) {
            if(strpos($this->module->getHtml(), '<!-- include:js -->', 0) !== false) {
                $parsed = str_replace('<!-- include:js -->', "<script src=\"/modules/js/{$this->getName()}.js\"></script>\r\n", $parsed);
            } else {
                $parsed .= "<script src=\"/modules/js/{$this->getName()}.js\"></script>\r\n";
            }
        }

        return eval("?>" . $parsed);
    }

}