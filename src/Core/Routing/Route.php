<?php

namespace MVC\Framework\Core\Routing;

use MVC\Framework\Core\Routing\Router;

class Route
{
    /**
     * Parse route handler
     *
     * @param mixed $handler    Array(container, action) / closure
     * 
     * @return array
     */
    private static function parseHandler(mixed $handler): array
    {
        $route = [];
        if (is_array($handler)) {
            if (isset($handler[0])) {
                $route['controller'] = $handler[0];
            }
            if (isset($handler[1])) {
                $route['action'] = $handler[1];
            } else {
                $route['action'] = 'index';
            }
        } else if (is_callable($handler)) {
            $route['callable'] = $handler;
        }
        return $route;
    }

    /**
     * Add route for GET request
     *
     * @param string $url           URL
     * @param mixed  $handler       Handler for url (array[class, action] or closure)
     * 
     * @return void
     */    
    public static function get(string $url, mixed $handler)
    {
        $route = self::parseHandler($handler);    
        $route['url'] = $url;
        $route['request'] = 'GET';        
        
        Router::addRoute($route);
        
    }
    /**
     * Add route for POST request
     *
     * @param string $url           URL
     * @param mixed  $handler       Handler for url (array[class, action] or closure)
     * 
     * @return void
     */
    public static function post(string $url, mixed $handler)
    {        
        $route = self::parseHandler($handler);            
        $route['url'] = $url;
        $route['request'] = 'POST';
        
        Router::addRoute($route);     
    }

    /**
     * Add route for PATCH request
     *
     * @param string $url           URL
     * @param mixed  $handler       Handler for url (array[class, action] or closure)
     * 
     * @return void
     */
    public static function patch(string $url, mixed $handler)
    {        
        $route = self::parseHandler($handler);            
        $route['url'] = $url;        
        $route['request'] = 'PATCH';

        Router::addRoute($route);
    }

    /**
     * Add route for DELETE request
     *
     * @param string $url           URL
     * @param mixed  $handler       Handler for url (array[class, action] or closure)
     * 
     * @return void
     */
    public static function delete(string $url, mixed $handler)
    {        
        $route = self::parseHandler($handler);            
        $route['url'] = $url;
        $route['request'] = 'DELETE';

        Router::addRoute($route);
    }

    /**
     * Add resource routes for URL (GET, POST, PATCH, DELETE)
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * 
     * @return void
     */
    public static function resource(string $url, string $controller): void
    {
        // get:     url             =>  controller@index
        self::get($url, [$controller,'index']);

        // get:     url/{id}        =>  controller@show(id)
        self::get($url . "/{id}", [$controller, 'show']);

        // get:     url/{id}/edit   =>  controller@edit(id)
        self::get($url . "/{id}/edit", [$controller,'edit']);

        // patch:   url/{id}        =>  controller@update(id)
        self::patch($url . "/{id}",  [$controller, 'update']);

        // get:     url/new         =>  controller@create
        self::get($url . "/new", [$controller, 'create']);

        // post:    url             =>  controller@store
        self::post($url,  [$controller, 'store']);

        // get:     url/{id}/delete =>  controller@delete(id)
        self::get($url . "/{id}/delete", [$controller, 'delete']);

        // delete:  url/{id}        =>  controller@destroy(id)
        self::delete($url . "/{id}", [$controller, 'destroy']);
    }

    /**
     * Remove route from routes
     *
     * @param string $url           URL
     * @param string $request       REQUEST_METHOD (i.e. GET, POST, PATCH, DELETE)
     * 
     * @return void
     */
    public static function remove(string $url, string $request)
    {
        Router::removeRoute(url: $url, request: $request);
    }
}