<?php

namespace MVC\Framework\Base;

use MVC\Framework\Base\View;

abstract class Controller
{
    protected function view($name, $data = []) {

        View::render($name, $data);
        
    }
}