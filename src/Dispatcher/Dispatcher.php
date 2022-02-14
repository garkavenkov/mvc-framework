<?php

namespace MVC\Framework\Dispatcher;

class Dispatcher
{

    protected $defaultController = 'Home';
    protected $defaultAction = 'index';

    protected $params = [];

    protected $queryString = '';

    protected $routeTemplates = array(
        // Controller/{default action: index}
        '/^(?<controller>[A-z]+)$/',
        // Controller/{Id}/{default action: show}
        '/^(?<controller>[A-z]+)\/(?<id>[\d]+)$/',
        // Controller/{Id}/Action
        '/^(?<controller>[A-z]+)\/(?<id>[\d]+)\/(?<action>[A-z]+)$/',
    );

    public $routes = array(
        ['posts'    =>  ['PostController', 'index']],
        ['users'    =>  ['UserController', 'index']],
    );

    public function dispatch($url)
    {
        $url = $this->removeQueryString($url);
        
        $this->parseUrl($url);

        $this->match($url);
    }

    protected function removeQueryString($url)
    {        
        if ($url != '') {
            $parts = explode('?', $url, 2);     
            if ($parts) {
                $url = $parts[0];                
            }
            if (isset($parts[1])) {
                $this->query_string = $parts[1];                
            }            
        }
        return $url;
    }

    public function match($url)
    {
        // Controller
        if (isset($this->params['controller']) && !is_null($this->params['controller'])) {
            $controller = $this->params['controller'];
        } else {
            $controller = $this->defaultController;
        }
        $controller = 'App\\Http\\Controllers\\' . ucfirst($controller) . 'Controller';
        

        // Parameter
        if (isset($this->params['id']) && !is_null($this->params['id'])) {
            $id = $this->params['id'];
        } else {
            $id = null;
        }
        
        // Action
        if (isset($this->params['action']) && !is_null($this->params['action'])) {
            $action = $this->params['action'];
        } else if ($id) {
            $action = 'show';
        } else {
            $action = $this->defaultAction;
        }
        
        if (class_exists($controller)) {
            $controllerObj = new $controller();            
            if (method_exists($controllerObj, $action)) {
                // $controllerObj->$action($id);
                // $controllerObj->index();
                if (is_callable([$controllerObj, $action])) {                 
                    $controllerObj->$action($id);
                    // $controllerObj->index();
                } else {
                    echo "$action isn't callable";
                    die();
                }
            } else {
                echo "Action '$action' not found in '$controller'";
                die();
            }

        }

    }

    private function parseUrl($url)
    {
        $url = preg_replace('/^\//', '', $url);
        $url = preg_replace('/\/$/', '', $url);
        
        foreach($this->routeTemplates as $template) {                    
            if (preg_match($template, $url, $matches)) {                
                foreach ($matches as $key => $value) {
                    if(is_string($key)) {
                        $this->params[$key] = $value;
                    }
                }
                // vd($this->params);
                return true;
            } 
        }        
        return false;
    }
}
