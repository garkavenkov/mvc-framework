<?php

namespace MVC\Framework\Core\Routing;

use MVC\Framework\Core\Routing\Router;

class Route
{
    /**
     * Add route for GET request
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * @param string $action        Action mathod
     * 
     * @return void
     */
    public static function get(string $url, string $controller, string $action)
    {
        Router::addRoute(url: $url, controller: $controller, action: $action, request: 'GET');
    }

    /**
     * Add route for POST request
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * @param string $action        Action method (by defaul 'store')
     * 
     * @return void
     */
    public static function post(string $url, string $controller, string $action='store')
    {
        Router::addRoute(url: $url, controller: $controller, action: $action, request: 'POST');     
    }

    /**
     * Add route for PATCH request
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * @param string $action        Action method (by default 'update)
     * 
     * @return void
     */
    public static function patch(string $url, string $controller, string $action='update')
    {
        Router::addRoute(url: $url, controller: $controller, action: $action, request: 'PATCH');
    }

    /**
     * Add route for DELETE request
     *
     * @param string $url           URL
     * @param string $controller    Controller class
     * @param string $action        Action method (by default 'destroy')
     * 
     * @return void
     */
    public static function delete(string $url, string $controller, string $action='destroy')
    {
        Router::addRoute(url: $url, controller: $controller, action: $action, request: 'DELETE');
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
        self::get(url: $url, controller: $controller, action: 'index');

        // get:     url/{id}        =>  controller@show(id)
        self::get(url: $url . "/{id}", controller: $controller, action: 'show');

        // get:     url/{id}/edit   =>  controller@edit(id)
        self::get(url: $url . "/{id}/edit", controller: $controller, action: 'edit');

        // patch:   url/{id}        =>  controller@update(id)
        self::patch(url: $url . "/{id}",  controller: $controller);

        // get:     url             =>  controller@create
        self::get(url: $url, controller: $controller, action: 'create');

        // post:    url             =>  controller@store
        self::post(url: $url . "/{id}",  controller: $controller);

        // get:     url/{id}/delete =>  controller@delete(id)
        self::get(url: $url . "/{id}/delete", controller: $controller, action: 'delete');

        // delete:  url/{id}        =>  controller@destroy(id)
        self::delete(url: $url . "/{id}", controller: $controller);        
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