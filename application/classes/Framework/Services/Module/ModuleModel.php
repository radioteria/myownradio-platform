<?php

namespace Tools;


use Framework\Services\Database;
use Framework\Services\HttpRequest;

class ModuleModel implements SingletonInterface {

    use Singleton;

    function parseModules($contents, $history = array(), $_MODULE = null) {
        return preg_replace_callback("/\\<\\!\\-\\-\\s+module\\:(.+)\\s+\\-\\-\\>/", function ($match) use ($history, $_MODULE) {
            return $this->getModule($match[1], $history, $_MODULE);
        }, $contents);
    }

    function getModuleNameByAlias($alias) {
        return Database::getInstance()->fetchOneColumn("SELECT name FROM r_modules WHERE alias = ? LIMIT 1", [$alias])
            ->getOrElseNull();
    }

    static function getModule($data, $history = array(), $_MODULE = NULL) {

        /* prevent recursion  */
        if (is_int(array_search($data, $history))) {
            return sprintf("Recursive call: Module \"%s\" called again from module \"%s\"!", $data, end($history));
        }

        $r = null;
        if (self::moduleExists($data)) {

            $history[] = $data;
            $module = self::fetchModule($data);

            if (HttpRequest::getInstance()->getMethod() === "GET") {
                $module_content = $module['html'];

                // CSS Style Sheet
                if (strlen($module['css']) > 0) {
                    if (strpos($module['html'], '<!-- include:css -->', 0) !== false) {
                        $module_content = str_replace('<!-- include:css -->', "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$data}.css\" />\r\n", $module_content);
                    } else {
                        $module_content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$data}.css\" />\r\n";
                    }
                }

                // Templates
                if (strlen($module['tmpl']) > 0) {
                    if (strpos($module['html'], '<!-- include:tmpl -->', 0) !== false) {
                        $module_content = str_replace('<!-- include:tmpl -->', $module['tmpl'], $module_content);
                    } else {
                        $module_content .= $module['tmpl'];
                    }
                }

                // JavaScript
                if (strlen($module['js']) > 0) {
                    if (strpos($module['html'], '<!-- include:js -->', 0) !== false) {
                        $module_content = str_replace('<!-- include:js -->', "<script src=\"/modules/js/{$data}.js\"></script>\r\n", $module_content);
                    } else {
                        $module_content .= "<script src=\"/modules/js/{$data}.js\"></script>\r\n";
                    }
                }

                $module_content = self::parseModules($module_content, $history, $_MODULE);
                $r = misc::execute($module_content, $_MODULE);
            } else {
                $r = misc::execute($module['post'], $_MODULE);
            }

        } else {
            $r = sprintf("Module \"%s\" not found!", $data);
        }
        return $r;
    }

    static function moduleExists($name) {
        return Database::getInstance()->fetchOneColumn("SELECT COUNT(*) FROM r_modules WHERE name = ?", [$name])
            ->getOrElseFalse();
    }

    static function fetchModule($name) {
        return Database::getInstance()
            ->fetchOneRow("SELECT *, UNIX_TIMESTAMP(modified) as unixmtime FROM r_modules WHERE name = ?", [$name])
            ->getOrElseNull();
    }

    static function printModulesJavaScript() {
        $rows = Database::getInstance()->fetchAll("SELECT js FROM r_modules WHERE 1");
        foreach ($rows as $row) {
            if ($row === null || strlen($row['js']) === 0) continue;
            echo $row['js'];
            echo "\n";
        }
    }

    static function printModulesCSS() {
        $rows = Database::getInstance()->fetchAll("SELECT css FROM r_modules WHERE 1");
        foreach ($rows as $row) {
            if ($row === null || strlen($row['css']) === 0) continue;
            echo $row['css'];
            echo "\n";
        }
    }
}
