<?php

class router
{
    
    private $route = null;
    private $parsedRoute = null;
    private $privateZones = array(
        'radiomanager'
    );

    public function router()
    {
        $this->route = preg_replace('/(\.(html|php)$)|(\/$)/', '', application::get('route', 'index', REQ_STRING));
        $this->parsedRoute = explode("/", $this->route);
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: origin, content-type");
    }
    
    public function start()
    {
        // todo: fix it
        application::setRoute($this->route);

        $this->runModule() ? null : $this->runController();
    }
    
    private function runModule()
    {
        $module = module::getModuleNameByAlias($this->route);
        if($module !== null)
        {
            try
            {
                echo module::getModule($module, array(), application::getAll());
                return true;
            }
            catch(controllerException $ex)
            {
                throw $ex;
            }
            catch(Exception $ex)
            {
                throw new controllerException(null, null, $ex);
            }
        }
        return false;
    }
    
    private function runController()
    {
        $controller_path = "main";
        $method = "main";
        $class = strtolower(application::getMethod()) . "_controller";
        $route = $this->parsedRoute;

        if(count($route) > 1)
        {
            $method = array_pop($route);
            $controller_path = implode(".", $route);
        }
        elseif(count($route) === 1)
        {
            $method = $route[0];
        }

        $controller_file = "application/controllers/controller_" . $controller_path . ".php";

        if (!file_exists($controller_file))
        {
            throw new patDocumentNotFoundException();
        }

        include $controller_file;

        if (!class_exists($class))
        {
            throw new patDocumentNotFoundException();
        }

        $refl = new ReflectionClass($class);

        $nc = call_user_func(array($refl, "newInstance"), $this->route);

        if (!method_exists($nc, $method))
        {
            throw new patDocumentNotFoundException();
        }

        try 
        {
            return call_user_func(array($nc, $method));
        }
        catch(morException $ex)
        {
            throw $ex;
        }
    }

}
