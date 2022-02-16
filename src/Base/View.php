<?php 

namespace MVC\Framework\Base;

abstract class View 
{
    static function render($name, $data) {

        $view = VIEWS_DIR . $name . '.php';
        extract($data, EXTR_SKIP);

        if (file_exists($view)) {
            require $view;
        } else {
            exit("View $name does not exists");
        }
    }
}