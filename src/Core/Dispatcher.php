<?php

namespace MVC\Framework\Core;

class Dispatcher
{

    protected $query_string = '';

    protected $query_string_params = [];

    /**
     * Undocumented function
     *
     * @param string $url The URL
     * @return void
     */
    public function dispatch(string $url): void
    {        
        $url = $this->removeQueryString($url);
        echo "URL: $url<br>";
        echo "<pre>";
        echo "$this->query_string";
        echo "</pre>";

    }

    protected function removeQueryString($url): string
    {        
        if ($url !== '') {
            $parts = explode('?', $url, 2);     
            if ($parts) {
                $url = $parts[0];                
                $url = preg_replace('/^\//', '', $url);
                $url = preg_replace('/\/$/', '', $url);
            }
            if (isset($parts[1])) {
                $this->query_string = $parts[1]; // ??? is it necessary if I parse query string into an array of parameters?
                $this->parseQueryParams($parts[1]);
                // $this->parseQueryParams();
            }            
        }
        // return $this->cleanUrl($url);
        return $url;
    }

    protected function parseQueryParams(string $query_string): void
    {
        // $query_string = $_SERVER['QUERY_STRING'];
        // echo "QUERY_STRING: $query_string<br>";
        if ($query_string !== '') {            
            $params = explode('&', $query_string);
            if ($params) {
                foreach($params as $param) {
                    $parts = explode('=', $param, 2);
                    // echo "Param '$param' length is - " . count($parts) . '<br>'; 
                    if (count($parts) == 2) {
                        $this->query_string_params[$parts[0]] = $parts[1];
                    }
                }
            }
        } 
    }

    protected function cleanUrl(string $url): string 
    {
        $url = preg_replace('/^\//', '', $url);
        $url = preg_replace('/\/$/', '', $url);

        return $url;
    }

    public function getParams(): array 
    {
        return $this->query_string_params;
    }
}