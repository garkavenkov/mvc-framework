<?php

namespace MVC\Framework\Core\Dispatching;

use MVC\Framework\Core\Routing\Router;

class Dispatcher
{
    /**
     * Query string
     *
     * @var string
     */
    protected $query_string = '';

    /**
     * Query string parameters
     *
     * @var array
     */
    protected $query_string_params = [];

    /**
     * Controller class 
     *
     * @var string
     */
    protected $controller = '';

    /**
     * Action name
     *
     * @var string
     */
    protected $action = 'index';

    /**
     * URL params (i.d. id)
     *
     * @var array
     */
    protected $params = [];

    /**
     * Parse URL, instantiate controllers object.
     *
     * @param string $url The URL
     * @return void
     */
    public function dispatch(string $url): void
    {        
        $url = $this->removeQueryString($url);                
        $this->match($url);
    }

    /**
     * Remove query string from URL
     *
     * @param string $url       URL
     * 
     * @return string
     */
    protected function removeQueryString(string $url): string
    {   
        if ($url == '/') {
            return $url;
        } else if ($url !== '') {
            $parts = explode('?', $url, 2);     
            if ($parts) {
                $url = $parts[0];                
                // $url = preg_replace('/^\//', '', $url);
                $url = preg_replace('/\/$/', '', $url);
            }
            if (isset($parts[1])) {
                $this->query_string = $parts[1]; // ??? is it necessary if I parse query string into an array of parameters?
                $this->parseQueryString($parts[1]);                
            }            
        }        
        return $url;
    }

    /**
     * Parse query string for pairs 'param' => 'value'
     *
     * @param string $query_string  Query string
     * 
     * @return void
     */
    protected function parseQueryString(string $query_string): void
    {        
        if ($query_string !== '') {            
            $params = explode('&', $query_string);
            if ($params) {
                foreach($params as $param) {
                    $parts = explode('=', $param, 2);     
                    if (count($parts) == 2) {
                        $this->query_string_params[$parts[0]] = $parts[1];
                    }
                }
            }
        } 
    }    

    /**
     * Return query string params
     *
     * @return array
     */
    public function getQueryParams(): array 
    {
        return $this->query_string_params;
    }

    /**
     * Match the route to the routes in the routing table.
     * Call action in controller if route has been matched.
     *
     * @param string $url   URL
     * @return void
     */
    protected function match(string $url)
    {  
        // Spoof POST request for  _method variable (for PUT, PATCH and DELETE methods)
        $method = isset($_POST['_method']) ?  $_POST['_method'] : $_SERVER['REQUEST_METHOD'];
        $routes = Router::list(request: $method);       
     
        if ($routes) {     
            
            $matched_route = [];    

            foreach($routes as $route) {
                
                if (preg_match($route['pattern'], $url, $matches)) {                
                    if ($matches) {
                        unset($matches[0]);
                        $this->params = $matches;
                        $matched_route = $route;
                        break;
                    }                    
                }                
            }            

            if ($matched_route) {
                if (isset($matched_route['callable'])) {
                    $matched_route['callable']();
                } else {
                    $this->controller = $route['controller'];
                    $this->action = $route['action'];       
    
                    if ($this->controller !== '' && class_exists($this->controller)) {                
                        $obj = new $this->controller();
                        if (is_callable(array($obj,$this->action))) {                        
                            call_user_func_array(array($obj, $this->action), $this->params);
                        } else {
                            die("Action $this->action does not exist");
                        }
                    } else {
                        die("Class $this->controller not found");
                    }            
                }
            } else {
                die('404 should be here');
            }           
        }
    }
}