<?php

class application {

    private static $args = NULL;
    private static $objects = array();
    private static $utime = NULL;
    private static $route = "";
    static $listener_id = NULL;

    static function getMicroTime($realtime = false) {
        if (is_null(self::$utime) || ($realtime === true)) {
            self::$utime = microtime(true) * 1000;
        }
        return self::$utime;
    }

    static function singular() {
        $args = func_get_args();
        $serial = serialize($args);
        if (count($args) === 0) {
            return null;
        } else {
            $class = array_shift($args);
        }
        if (!isset(self::$objects{$serial})) {
            try {
                $refl = new ReflectionClass($class);
                self::$objects{$serial} = call_user_func_array(array($refl, "newInstance"), $args);
            } catch (Exception $ex) {
                throw new Exception("Can't create singular object!", null, $ex);
            }
        }
        return self::$objects{$serial};
    }

    static function singularDestroy() {
        $args = func_get_args();
        $serial = serialize($args);
        if (isset(self::$objects{$serial})) {
            unset(self::$objects{$serial});
            return true;
        }
        return false;
    }
    
    static function saveStat() {
        db::query_update("INSERT INTO `r_stats_memory` VALUES (NULL, ?, ?, ?, ?, ?, NOW())", array(
            application::getClient(),
            user::getCurrentUserId(),
            "http" . (isset($_SERVER['HTTPS']) ? "s" : "") .  "://" . filter_input(INPUT_SERVER, 'HTTP_HOST') . filter_input(INPUT_SERVER, 'REQUEST_URI'),
            filter_input(INPUT_SERVER, 'HTTP_REFERER'),
            filter_input(INPUT_SERVER, 'HTTP_USER_AGENT')
        ));
    }

    private static function init() {
        self::$args = array(
            'METHOD' =>
                filter_input(INPUT_SERVER, 'REQUEST_METHOD'),
            'LANG' =>
                substr(filter_input(INPUT_SERVER, 'HTTP_ACCEPT_LANGUAGE'), 0, 2),
            'GET' =>
                ! is_null(filter_input_array(INPUT_GET)) ?
                    filter_input_array(INPUT_GET) :
                    NULL,
            'POST' =>
                ! is_null(filter_input_array(INPUT_POST)) ?
                    filter_input_array(INPUT_POST) :
                    NULL,
            'CLIENT' =>
                ! is_null(filter_input(INPUT_SERVER, 'HTTP_X_REAL_IP')) ?
                    filter_input(INPUT_SERVER, 'HTTP_X_REAL_IP') :
                    filter_input(INPUT_SERVER, 'REMOTE_ADDR'),
            'ROOT' =>
                filter_input(INPUT_SERVER, 'DOCUMENT_ROOT'),
            'PATH' =>
                explode("?", filter_input(INPUT_SERVER, 'REQUEST_URI'))[0],
            'SITE' =>
                "http" . (isset($_SERVER['HTTPS']) ? "s" : "") .  "://" . filter_input(INPUT_SERVER, 'HTTP_HOST'),
            'HEADERS' =>
                http_get_request_headers()
        );
    }

    static function getHeader($key)
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return isset(self::$args['HEADERS'][$key]) ? self::$args['HEADERS'][$key] : null;
    }
    
    static function getClient()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args['CLIENT'];
    }

    static function getPath()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args['PATH'];
    }

    static function getSite()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args['SITE'];
    }
    
    static function setRoute($route)
    {
        self::$route = $route;
    }
 
    static function getRoute()
    {
        return self::$route;
    }

    static function testRoute($route, $yes = "", $no = "")
    {
        if(self::$route === $route)
        {
            return $yes;
        }
        else
        {
            return $no;
        }
    }

    static function getLanguage()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args['LANG'];
    }

    static function getApplication()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args;
    }

    static function getMethod()
    {
        if (empty(self::$args))
        {
            self::init();
        }
        return self::$args['METHOD'];
    }

    static function getRoot()
    {
        return self::$args['ROOT'];
    }

    /**
     * @deprecated
     * @param $param
     * @param $default
     * @param $type
     * @return bool|int|null|string
     */
    static function get($param, $default = NULL, $type = "string")
    {
        if (empty(self::$args))
        {
            self::init();
        }
        if (!isset(self::$args['GET'][$param]))
        {
            return $default;
        }
        switch ($type)
        {
            case 'string':
                return (string) self::$args['GET'][$param];
            case 'int':
                return (int) self::$args['GET'][$param];
            case 'bool':
                return (self::$args['GET'][$param] === 'true') ? true : false;
            default:
                return self::$args['GET'][$param];
        }
    }
    
    static function getAll($exclude = array())
    {
        if (empty(self::$args))
        {
            self::init();
        }
        
        $buffer = array();
        if(isset(self::$args['GET']))
        {
            foreach(self::$args['GET'] as $key=>$get)
            {
                if(array_search($key, $exclude) === false)
                {
                    $buffer[$key] = $get;
                }
            }
        }
        return $buffer;
    }

    /**
     * @deprecated
     * @param $param
     * @param $default
     * @param $type
     * @return bool|int|null|string
     */
    static function post($param, $default = NULL, $type = null)
    {
        if (empty(self::$args))
        {
            self::init();
        }
        if (!isset(self::$args['POST'][$param]))
        {
            return $default;
        }

        switch ($type)
        {
            case 'string':
                return (string) self::$args['POST'][$param];
            case 'int':
                return (int) self::$args['POST'][$param];
            case 'bool':
                return (self::$args['POST'][$param] === 'true') ? true : false;
            default:
                return self::$args['POST'][$param];
        }
    }

    /**
     * @param $param
     * @return Optional
     */
    static function getParamOptional($param) {
        if (empty(self::$args)) {
            self::init();
        }
        return Optional::ofNull(self::$args['GET']);
    }

    /**
     * @param $param
     * @return Optional
     */
    static function getPostOptional($param) {
        if (empty(self::$args)) {
            self::init();
        }
        return Optional::ofNull(self::$args['POST']);
    }


    static function getDocTitle($title)
    {
        return str_replace('%TITLE%', $title, config::getSetting("content", "document_title"));
    }
    
    static function getProfileStats()
    {
        $user_id = user::getCurrentUserId();
        
        if ($user_id === 0)
        {
            return null;
        }
        
        $visitor = new Visitor($user_id);
        $plan    = new VisitorPlan($user_id);
        $stats   = new VisitorStats($user_id);
        
        return array(
            'user_data'  => $visitor->getStatus(),
            'plan_data'  => $plan->getStatus(),
            'user_stats' => $stats->getStatus()
        );
    }
    
}
