<?php

namespace MVC\Framework\Core\Routing;

class Router
{
    protected static $_routes = [];

    /**
     * Return registered routes
     *
     * @param string $request       REQUEST_METHOD
     * @param string $controller    Controller class
     * @param string $action        Action name
     * 
     * @return array                Routes list
     */
    public static function list(string $request = '', string $controller = '', string $action = ''): array
    {        
        $routes = self::$_routes; 
        
        if ($request !== '') {            
            $routes = array_filter(self::$_routes, function($route) use ($request) {                
                return $route['request'] == $request;
            });
            
        }

        if ($controller !== '') {        
            $routes = array_filter(self::$_routes, function($route) use ($controller) {
                return str_contains($route['controller'], $controller);
            });
        }

        if ($action !== '') {            
            $routes = array_filter(self::$_routes, function($route) use ($action) {
                return str_contains($route['action'], $action);                
            });
        }

        return $routes;
    }

    /**
     * Convert url into regular expression pattern
     *
     * @param string $url   URL
     * 
     * @return string       Regular expression pattern
     */
    private static function convertUrlToPattern(string $url): string
    {        
        if ($url == '') {
            $url = '/^$/';
        } else {
            $url = str_replace('/', '\/', $url);     
            $url = preg_replace('/\{(\w+)\}/', '(\w+)', $url);  
            $url = '/' .$url . '$/';
        }

        return $url;
    }

    /**
     * Add route to the routes
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * @param string $action        Action method
     * @param string $request       REQUEST_METHOD (i.e. GET, POST, PATCH, DELETE)
     * 
     * @return void
     */
    public static function addRoute(string $url, string $controller, string $action, string $request): void
    {
        self::$_routes[] = array(
                'url' => self::convertUrlToPattern($url), 
                'controller' => $controller, 
                'action' => $action, 
                'request' => strtoupper($request)
            );
    }
   
    /**
     * Remove route form the routes
     *
     * @param string $url       URL
     * @param string $request   REQUEST_METHOD
     * 
     * @return void
     */
    public static function removeRoute(string $url, string $request): void
    {
        $routes = self::list(request: strtoupper($request));
        $url = self::convertUrlToPattern($url);        
        if ($routes) {
            foreach($routes as $id => $route) {
                if ($route['url'] == $url) {                    
                    unset(self::$_routes[$id]);
                    break;
                }
            }   
        }
    }
}