<?php

class module
{

    static function parseModules($contents, $history = array(), $_MODULE = null)
    {
        return preg_replace_callback("/\<\!\-\-\s+module\:(.+)\s+\-\-\>/", function ($match) use ($history, $_MODULE)
        {
            return self::getModule($match[1], $history, $_MODULE);
        }, $contents);
    }

    static function getModuleNameByAlias($alias)
    {
        return db::query_single_col("SELECT `name` FROM `r_modules` WHERE `alias` = ? LIMIT 1", array($alias));
    }

    static function getModule($data, $history = array(), $_MODULE = NULL)
    {
        /* prevent recursion  */
        if (is_int(array_search($data, $history)))
        {
            return sprintf("Recursive call: Module \"%s\" called again from module \"%s\"!", $data, end($history));
        }

        $module_path = sprintf("application/modules/mod.%s.php", $data);
        $r = NULL;
        if (file_exists($module_path))
        {
            $history[] = $data;
            $module_content = file_get_contents($module_path); 
            $module_content = self::parseModules($module_content, $history, $_MODULE);
            $r = misc::execute($module_content, $_MODULE); 
        }
        else if(self::moduleExists($data)) 
        {
            $history[] = $data;
            $module = self::fetchModule($data);
            
            if(application::getMethod() === "GET")
            {
                $module_content = $module['html'];
                
                // CSS Style Sheet
                if(strlen($module['css']) > 0)
                {
                    if(strpos($module['html'], '<!-- include:css -->', 0) !== false)
                    {
                        $module_content = str_replace('<!-- include:css -->', "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$data}.css\" />\r\n", $module_content);
                    }
                    else
                    {
                        $module_content .= "<link rel=\"stylesheet\" type=\"text/css\" href=\"/modules/css/{$data}.css\" />\r\n";
                    }
                }

                // Templates
                if(strlen($module['tmpl']) > 0)
                {
                    if(strpos($module['html'], '<!-- include:tmpl -->', 0) !== false)
                    {
                        $module_content = str_replace('<!-- include:tmpl -->', $module['tmpl'], $module_content);
                    }
                    else
                    {
                        $module_content .= $module['tmpl'];
                    }
                }
                
                // JavaScript
                if(strlen($module['js']) > 0)
                {
                    if(strpos($module['html'], '<!-- include:js -->', 0) !== false)
                    {
                        $module_content = str_replace('<!-- include:js -->', "<script src=\"/modules/js/{$data}.js\"></script>\r\n", $module_content);
                    }
                    else
                    {
                        $module_content .= "<script src=\"/modules/js/{$data}.js\"></script>\r\n";
                    }
                }

                $module_content = self::parseModules( $module_content, $history, $_MODULE );
                $r = misc::execute( $module_content, $_MODULE ); 
            }
            else
            {
                $r = misc::execute( $module['post'], $_MODULE ); 
            } 
            
        }
        else
        {
            $r = sprintf("Module \"%s\" not found!", $data);
        }
        return $r;
    }

    static function moduleExists($name)
    {
        $redisKey = "myownradio.biz:modules:{$name}";
        if(myredis::handle()->exists($redisKey))
        {
            return true;
        }
        return db::query_single_col("SELECT COUNT(*) FROM `r_modules` WHERE `name` = ?", array($name));
    }

    static function fetchModule($name)
    {
        /*
        $redisKey = "myownradio.biz:modules:{$name}";
        if(myredis::handle()->exists($redisKey))
        {
            $keys = array("html", "css", "js", "tmpl", "post");
            $return = myredis::handle()->hgetall($redisKey);
            foreach($keys as $key)
            {
                if(!isset($return[$key]))
                {
                    $return[$key] = "";
                }
            }
        } */
        return db::query_single_row("SELECT *, UNIX_TIMESTAMP(`modified`) as `unixmtime` FROM `r_modules` WHERE `name` = ?", array($name));
    }

    static function printModulesJavaScript() {
        $rows = db::query("SELECT `js` FROM `r_modules` WHERE 1");
        foreach ($rows as $row) {
            if ($row === null || strlen($row['js']) === 0) continue;
            echo $row['js'];
            echo "\n";
        }
    }

    static function printModulesCSS() {
        $rows = db::query("SELECT `css` FROM `r_modules` WHERE 1");
        foreach ($rows as $row) {
            if ($row === null || strlen($row['css']) === 0) continue;
            echo $row['css'];
            echo "\n";
        }
    }
}
