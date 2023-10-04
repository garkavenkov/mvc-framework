<?php

namespace MVC\Framework\Console;

use MVC\Framework\Console\Commands\MakeCommand;
use MVC\Framework\Console\Commands\Server;
use MVC\Framework\Console\Commands\MakeModel;
use MVC\Framework\Console\Commands\RouteList;
use MVC\Framework\Console\Commands\MakeController;

class Runner
{
    /**
     * Available commands
     *
     * @var array
     */
    private $commands = [        
        'server'            =>  Server::class,
        'make:controller'   =>  MakeController::class,
        'make:model'        =>  MakeModel::class,
        'make:command'      =>  MakeCommand::class,
        'route:list'        =>  RouteList::class,
    ];    

    /**
     * Constructor
     */
    public function __construct()
    {
        $file_name = CONFIG_DIR . '/commands.php';
        if (file_exists($file_name)) {
            $config_commands = include $file_name;
            if ($config_commands) {
                $this->commands = array_merge($this->commands, $config_commands);
            }
        }
    }

    /**
     * Output available commands list
     *
     * @return void
     */
    private function availableCommandsList()
    {
        echo "Available commands:\n";
        foreach($this->commands as $command => $info) {
            echo "$command - " . $info . PHP_EOL;
        }

    }

    /**
     * Call command help function
     *
     * @param array $argv   Arguments
     * @return void
     */
    private function displayCommandHelp(array $argv)
    {
        $command =  array_shift($argv);
        if (array_key_exists($command, $this->commands)) {
            call_user_func(array(new $this->commands[$command], 'help'));
        }
    }

    /**
     * Command runner
     *
     * @param array $argv   Arguments
     * @return void
     */
    public function run(array $argv)
    {        
        if (count($argv) == 1) {  
            echo  "Usage:\n";
            echo  "  command [arguments] [options]\n";
            exit(0);
        } else {
            array_shift($argv);
        }
        
        $command = array_shift($argv);
        
        if (array_key_exists($command, $this->commands)) {            
            call_user_func_array(array(new $this->commands[$command], 'handler'), [$argv]);
        } elseif ($command == 'list') {    
            $this->availableCommandsList();
        } elseif ($command == 'help') {    
            $this->displayCommandHelp($argv);
        } else {
            exit("Command $command does not exists.\n");
        }
    }
}