<?php

namespace MVC\Framework\Console\Commands;

use MVC\Framework\Core\Routing\Router;
use AdvancedPrint\AdvancedPrint as AP;

class RouteList extends ConsoleCommand
{
    /**
     * Command usage
     *
     * @var string
     */
    protected $command = "mvc route:list [параметри]";

    /**
     * Command description
     *
     * @var string
     */
    protected $description = "Відображає список зареєстрованих URL";
    
    /**
     * Command arguments description if there are any
     *
     * @var array
     */
    protected $arguments = array(
        '--request|-r=<request_method>' =>  'Відобразити URL які пов`язані з HTTP методом [U_Yellow]request_method*',
        '--controller|-c=<Controller>'  =>  'Відобразити URL які пов`язані с контроллером <Controller>',
        '--action|-a=<action>'          =>  'Відобразити URL які пов`язані с методом [U_Yellow]action*'
    );

    /**
     * Command usage example(s)
     *
     * @var array
     */
    protected $examples = array(
        'mvc route:list --request=GET'  =>  'Відобразить зареєстровані URL з HTTP[\'REQUEST_METHOD\']=GET',
        'mvc route:list --action=index' =>  'Відобразить зареєстровані URL які пов`язані с [B_White]\'index\' [Reset]методами контроллерів',
    );

    /**
     * Footnotes description, if there are any
     *
     * @var array
     */
    protected $footnotes = array(
        'request_method*'    =>  'HTTP Request Method: (GET, POST, PATCH, DELETE)',
        'action*'            =>  'Метод контроллера: (index, show, create, store і т.п.)'
    );

    /**
     * Command handler
     *
     * @param array $argv   Command arguments
     * @return void
     */
    public function handler(array $argv)
    {
        Router::loadRoutes();  

        $request = '';
        $controller = '';
        $action = '';
        
        if ($argv) {    
            foreach($argv as $argument) {
                $parts = explode('=', $argument);      
                if (count($parts) == 2) {
                    if (($parts[0] == '--request') || ($parts[0] == '-r') )
                    {
                        $request = $parts[1];
                    }
                    if (($parts[0] == '--controller') || ($parts[0] == '-c')) {
                        $controller = $parts[1];
                    }
                    if (($parts[0] == '--action') || ($parts[0] == '-a')) {
                        $action = $parts[1];
                    }
                }      
            }
        }   
        $routes = Router::list(request: $request, controller: $controller, action: $action);
        
        if ($routes) {
            foreach($routes as $route) {
                AP::print("[B_Yellow]|");
                AP::print("[Blue]" . str_pad($route['request'], 8, ' '));
                AP::print("[B_Yellow]|")  ;
                AP::print("[Green]" . str_pad($route['url'], 20, ' '));
                AP::print("[B_Yellow]|");
                if (isset($route['callable'])) {    
                    AP::print("[Blue]" . str_pad('funciton', 70, ' '));          
                } else {
                    $controller_length = strlen($route['controller']);       
                    AP::print("[Blue]" . $route['controller']);
                    AP::print("[White]@");
                    AP::print("[Red]" . str_pad($route['action'], 70 - ($controller_length +1), ' '));      
                }
                AP::printLn("[B_Yellow]|")  ;      
            }
        } else {
            echo "\n";
            AP::printLn("[B_Yellow]Routing table is empty.");
            echo "\n";
        }
    }
}