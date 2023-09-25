<?php

namespace MVC\Framework\Base;

class View
{
    public static function render(string $view, array $params = [])
    {
        
        $file = VIEWS_DIR . '/' . $view .  (!str_ends_with($view, '.php') ? '.php' : '');

        extract($params, EXTR_SKIP);

        if (is_readable($file)) {
            require $file;            
        } else {
            die("$view does not exist");
        }
    }
}